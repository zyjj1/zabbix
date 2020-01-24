/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

#include "checks_ssh.h"

#if defined(HAVE_SSH2)
#include <libssh2.h>
#elif defined (HAVE_SSH)
#include <libssh/libssh.h>
#endif

#if defined(HAVE_SSH2) || defined(HAVE_SSH)
#include "comms.h"
#include "log.h"

#define SSH_RUN_KEY	"ssh.run"
#endif

#if defined(HAVE_SSH2)
static const char	*password;

static void	kbd_callback(const char *name, int name_len, const char *instruction,
		int instruction_len, int num_prompts,
		const LIBSSH2_USERAUTH_KBDINT_PROMPT *prompts,
		LIBSSH2_USERAUTH_KBDINT_RESPONSE *responses, void **abstract)
{
	(void)name;
	(void)name_len;
	(void)instruction;
	(void)instruction_len;

	if (num_prompts == 1)
	{
		responses[0].text = zbx_strdup(NULL, password);
		responses[0].length = strlen(password);
	}

	(void)prompts;
	(void)abstract;
}

static int	waitsocket(int socket_fd, LIBSSH2_SESSION *session)
{
	struct timeval	tv;
	int		rc, dir;
	fd_set		fd, *writefd = NULL, *readfd = NULL;

	tv.tv_sec = 10;
	tv.tv_usec = 0;

	FD_ZERO(&fd);
	FD_SET(socket_fd, &fd);

	/* now make sure we wait in the correct direction */
	dir = libssh2_session_block_directions(session);

	if (0 != (dir & LIBSSH2_SESSION_BLOCK_INBOUND))
		readfd = &fd;

	if (0 != (dir & LIBSSH2_SESSION_BLOCK_OUTBOUND))
		writefd = &fd;

	rc = select(socket_fd + 1, readfd, writefd, NULL, &tv);

	return rc;
}

/* example ssh.run["ls /"] */
static int	ssh_run(DC_ITEM *item, AGENT_RESULT *result, const char *encoding)
{
	const char	*__function_name = "ssh_run";
	zbx_socket_t	s;
	LIBSSH2_SESSION	*session;
	LIBSSH2_CHANNEL	*channel;
	int		auth_pw = 0, rc, ret = NOTSUPPORTED,
			exitcode, bytecount = 0;
	char		buffer[MAX_BUFFER_LEN], buf[16], *userauthlist,
			*publickey = NULL, *privatekey = NULL, *ssherr, *output;
	size_t		sz;

	zabbix_log(LOG_LEVEL_DEBUG, "In %s()", __function_name);

	if (FAIL == zbx_tcp_connect(&s, CONFIG_SOURCE_IP, item->interface.addr, item->interface.port, 0,
			ZBX_TCP_SEC_UNENCRYPTED, NULL, NULL))
	{
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot connect to SSH server: %s", zbx_socket_strerror()));
		goto close;
	}

	/* initializes an SSH session object */
	if (NULL == (session = libssh2_session_init()))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot initialize SSH session"));
		goto tcp_close;
	}

	/* set blocking mode on session */
	libssh2_session_set_blocking(session, 1);

	/* Create a session instance and start it up. This will trade welcome */
	/* banners, exchange keys, and setup crypto, compression, and MAC layers */
	if (0 != libssh2_session_startup(session, s.socket))
	{
		libssh2_session_last_error(session, &ssherr, NULL, 0);
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot establish SSH session: %s", ssherr));
		goto session_free;
	}

	/* check what authentication methods are available */
	if (NULL != (userauthlist = libssh2_userauth_list(session, item->username, strlen(item->username))))
	{
		if (NULL != strstr(userauthlist, "password"))
			auth_pw |= 1;
		if (NULL != strstr(userauthlist, "keyboard-interactive"))
			auth_pw |= 2;
		if (NULL != strstr(userauthlist, "publickey"))
			auth_pw |= 4;
	}
	else
	{
		libssh2_session_last_error(session, &ssherr, NULL, 0);
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot obtain authentication methods: %s", ssherr));
		goto session_close;
	}

	zabbix_log(LOG_LEVEL_DEBUG, "%s() supported authentication methods:'%s'", __function_name, userauthlist);

	switch (item->authtype)
	{
		case ITEM_AUTHTYPE_PASSWORD:
			if (auth_pw & 1)
			{
				/* we could authenticate via password */
				if (0 != libssh2_userauth_password(session, item->username, item->password))
				{
					libssh2_session_last_error(session, &ssherr, NULL, 0);
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Password authentication failed: %s",
							ssherr));
					goto session_close;
				}
				else
					zabbix_log(LOG_LEVEL_DEBUG, "%s() password authentication succeeded",
							__function_name);
			}
			else if (auth_pw & 2)
			{
				/* or via keyboard-interactive */
				password = item->password;
				if (0 != libssh2_userauth_keyboard_interactive(session, item->username, &kbd_callback))
				{
					libssh2_session_last_error(session, &ssherr, NULL, 0);
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Keyboard-interactive authentication"
							" failed: %s", ssherr));
					goto session_close;
				}
				else
					zabbix_log(LOG_LEVEL_DEBUG, "%s() keyboard-interactive authentication succeeded",
							__function_name);
			}
			else
			{
				SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Unsupported authentication method."
						" Supported methods: %s", userauthlist));
				goto session_close;
			}
			break;
		case ITEM_AUTHTYPE_PUBLICKEY:
			if (auth_pw & 4)
			{
				if (NULL == CONFIG_SSH_KEY_LOCATION)
				{
					SET_MSG_RESULT(result, zbx_strdup(NULL, "Authentication by public key failed."
							" SSHKeyLocation option is not set"));
					goto session_close;
				}

				/* or by public key */
				publickey = zbx_dsprintf(publickey, "%s/%s", CONFIG_SSH_KEY_LOCATION, item->publickey);
				privatekey = zbx_dsprintf(privatekey, "%s/%s", CONFIG_SSH_KEY_LOCATION,
						item->privatekey);

				if (SUCCEED != zbx_is_regular_file(publickey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot access public key file %s",
							publickey));
					goto session_close;
				}

				if (SUCCEED != zbx_is_regular_file(privatekey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot access private key file %s",
							privatekey));
					goto session_close;
				}

				rc = libssh2_userauth_publickey_fromfile(session, item->username, publickey,
						privatekey, item->password);

				if (0 != rc)
				{
					libssh2_session_last_error(session, &ssherr, NULL, 0);
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Public key authentication failed:"
							" %s", ssherr));
					goto session_close;
				}
				else
					zabbix_log(LOG_LEVEL_DEBUG, "%s() authentication by public key succeeded",
							__function_name);
			}
			else
			{
				SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Unsupported authentication method."
						" Supported methods: %s", userauthlist));
				goto session_close;
			}
			break;
	}

	/* exec non-blocking on the remove host */
	while (NULL == (channel = libssh2_channel_open_session(session)))
	{
		switch (libssh2_session_last_error(session, NULL, NULL, 0))
		{
			/* marked for non-blocking I/O but the call would block. */
			case LIBSSH2_ERROR_EAGAIN:
				waitsocket(s.socket, session);
				continue;
			default:
				SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot establish generic session channel"));
				goto session_close;
		}
	}

	dos2unix(item->params);	/* CR+LF (Windows) => LF (Unix) */
	/* request a shell on a channel and execute command */
	while (0 != (rc = libssh2_channel_exec(channel, item->params)))
	{
		switch (rc)
		{
			case LIBSSH2_ERROR_EAGAIN:
				waitsocket(s.socket, session);
				continue;
			default:
				SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot request a shell"));
				goto channel_close;
		}
	}

	for (;;)
	{
		/* loop until we block */
		do
		{
			if (0 < (rc = libssh2_channel_read(channel, buf, sizeof(buf))))
			{
				sz = (size_t)rc;
				if (sz > (size_t)(MAX_BUFFER_LEN - (bytecount + 1)))
					sz = (size_t)(MAX_BUFFER_LEN - (bytecount + 1));
				if (0 == sz)
					continue;

				memcpy(buffer + bytecount, buf, sz);
				bytecount += sz;
			}
		}
		while (rc > 0);

		/* this is due to blocking that would occur otherwise so we loop on
		 * this condition
		 */
		if (LIBSSH2_ERROR_EAGAIN == rc)
			waitsocket(s.socket, session);
		else if (rc < 0)
		{
			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot read data from SSH server"));
			goto channel_close;
		}
		else
			break;
	}

	buffer[bytecount] = '\0';

	output = convert_to_utf8(buffer, bytecount, encoding);
	zbx_rtrim(output, ZBX_WHITESPACE);

	if (SUCCEED == set_result_type(result, ITEM_VALUE_TYPE_TEXT, output))
		ret = SYSINFO_RET_OK;

	zbx_free(output);
channel_close:
	/* close an active data channel */
	exitcode = 127;
	while (LIBSSH2_ERROR_EAGAIN == (rc = libssh2_channel_close(channel)))
		waitsocket(s.socket, session);

	if (0 != rc)
	{
		libssh2_session_last_error(session, &ssherr, NULL, 0);
		zabbix_log(LOG_LEVEL_WARNING, "%s() cannot close generic session channel: %s", __function_name, ssherr);
	}
	else
		exitcode = libssh2_channel_get_exit_status(channel);

	zabbix_log(LOG_LEVEL_DEBUG, "%s() exitcode:%d bytecount:%d", __function_name, exitcode, bytecount);

	libssh2_channel_free(channel);
	channel = NULL;

session_close:
	libssh2_session_disconnect(session, "Normal Shutdown");

session_free:
	libssh2_session_free(session);

tcp_close:
	zbx_tcp_close(&s);

close:
	zbx_free(publickey);
	zbx_free(privatekey);
	zabbix_log(LOG_LEVEL_DEBUG, "End of %s():%s", __function_name, zbx_result_string(ret));

	return ret;
}
#elif defined(HAVE_SSH)

/* example ssh.run["ls /"] */
static int	ssh_run(DC_ITEM *item, AGENT_RESULT *result, const char *encoding)
{
	ssh_session	session;
	ssh_channel	channel;
	int		auth_pw = 0, rc, ret = NOTSUPPORTED, bytecount = 0;
	int		userauth;
	char		*publickey = NULL, *privatekey = NULL;
	ssh_key 	privkey, pubkey;
	int		priv_free = 0, pub_free = 0;
	char		buffer[MAX_BUFFER_LEN], buf[16], *output, userauthlist[64];
	size_t		offset = 0;
	size_t		sz;

	zabbix_log(LOG_LEVEL_DEBUG, "In %s()", __func__);

	/* initializes an SSH session object */
	if (NULL == (session = ssh_new()))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot initialize SSH session"));
		zabbix_log(LOG_LEVEL_DEBUG, "Cannot initialize SSH session");

		goto close;
	}

	/* set blocking mode on session */
	ssh_set_blocking(session, 1);

	/* Create a session instance and start it up */
	ssh_options_set(session, SSH_OPTIONS_HOST, item->interface.addr);
	ssh_options_set(session, SSH_OPTIONS_PORT, &item->interface.port);
	ssh_options_set(session, SSH_OPTIONS_USER, item->username);
	if (SSH_OK != ssh_connect(session))
	{
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot establish SSH session: %s", ssh_get_error(session)));
		goto session_free;
	}

	/* check what authentication methods are available */
	if (SSH_AUTH_ERROR == ssh_userauth_none(session, item->username))
	{
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Error during authentication: %s", ssh_get_error(session)));
		goto session_close;
	}

	userauthlist[0] = '\0';
	if (0 != (userauth = ssh_userauth_list(session, item->username)))
	{
		if (0 != (userauth & SSH_AUTH_METHOD_PASSWORD))
		{
			offset += zbx_snprintf(userauthlist + offset, sizeof(userauthlist) - offset, "password, ");
			auth_pw |= 1;

		}
		if (0 != (userauth & SSH_AUTH_METHOD_INTERACTIVE))
		{
			offset += zbx_snprintf(userauthlist + offset, sizeof(userauthlist) - offset,
					"keyboard-interactive, ");
			auth_pw |= 2;
		}
		if (0 != (userauth & SSH_AUTH_METHOD_PUBLICKEY))
		{
			offset += zbx_snprintf(userauthlist + offset, sizeof(userauthlist) - offset, "publickey, ");
			auth_pw |= 4;
		}
		if (0 != (SSH_AUTH_METHOD_HOSTBASED & userauth))
		{
			offset += zbx_snprintf(userauthlist + offset, sizeof(userauthlist) - offset, "hostbased, ");
		}
		userauthlist[offset-2] = '\0';
	}

	zabbix_log(LOG_LEVEL_DEBUG, "%s() supported authentication methods: %s", __func__, userauthlist);

	switch (item->authtype)
	{
		case ITEM_AUTHTYPE_PASSWORD:
			if (auth_pw & 1)
			{
				/* we could authenticate via password */
				if (SSH_AUTH_SUCCESS != ssh_userauth_password(session, item->username, item->password))
				{

					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Password authentication failed: %s",
							ssh_get_error(session)));
					goto session_close;
				}
				else
					zabbix_log(LOG_LEVEL_DEBUG, "%s() password authentication succeeded", __func__);

			}
			else if (auth_pw & 2)
			{
				/* or via keyboard-interactive */
				rc = ssh_userauth_kbdint(session, item->username, NULL);
				while (SSH_AUTH_INFO == rc)
				{
					if (1 == ssh_userauth_kbdint_getnprompts(session))
					{
						if (0 > ssh_userauth_kbdint_setanswer(session, 0, item->password))
						{
							zabbix_log(LOG_LEVEL_DEBUG,"Cannot set answer: %s",
									ssh_get_error(session));
						}
					}
					rc = ssh_userauth_kbdint(session, item->username, NULL);
				}

				if (SSH_AUTH_SUCCESS != rc)
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Keyboard-interactive authentication"
							" failed: %s", ssh_get_error(session)));
					goto session_close;
				}
				else
				{
					zabbix_log(LOG_LEVEL_DEBUG, "%s() keyboard-interactive authentication succeeded",
							__func__);
				}
			}
			else
			{
				SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Unsupported authentication method."
						" Supported methods: %s", userauthlist));
				goto session_close;
			}
			break;
		case ITEM_AUTHTYPE_PUBLICKEY:
			if (auth_pw & 4)
			{
				if (NULL == CONFIG_SSH_KEY_LOCATION)
				{
					SET_MSG_RESULT(result, zbx_strdup(NULL, "Authentication by public key failed."
							" SSHKeyLocation option is not set"));
						goto session_close;
				}

				/* or by public key */
				publickey = zbx_dsprintf(publickey, "%s/%s", CONFIG_SSH_KEY_LOCATION, item->publickey);
				privatekey = zbx_dsprintf(privatekey, "%s/%s", CONFIG_SSH_KEY_LOCATION,
						item->privatekey);

				if (SUCCEED != zbx_is_regular_file(publickey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot access public key file %s",
							publickey));
					goto session_close;
				}

				if (SUCCEED != zbx_is_regular_file(privatekey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Cannot access private key file %s",
							privatekey));
					goto session_close;
				}

				if (SSH_OK != ssh_pki_import_pubkey_file(publickey, &pubkey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Public key import failed:"));
					goto session_close;
				}
				pub_free = 1;

				if (SSH_AUTH_SUCCESS != ssh_userauth_try_publickey(session, item->username, pubkey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Public key try failed: %s",
							ssh_get_error(session)));
					goto session_close;
				}

				if (SSH_OK != ssh_pki_import_privkey_file(privatekey, NULL, NULL, NULL, &privkey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Private key impotr failed:"));
					goto session_close;
				}
				priv_free = 1;

				if (SSH_AUTH_SUCCESS != ssh_userauth_publickey(session, item->username, privkey))
				{
					SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Public key authentication failed:"
							" %s", ssh_get_error(session)));
					goto session_close;
				}
				else
					zabbix_log(LOG_LEVEL_DEBUG, "%s() authentication by public key succeeded",
							__func__);
			}
			else
			{
				SET_MSG_RESULT(result, zbx_dsprintf(NULL, "Unsupported authentication method."
						" Supported methods: %s", userauthlist));
				goto session_close;
			}
			break;
	}

	if (NULL == (channel = ssh_channel_new(session)))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot create generic session channel"));
		goto session_close;
	}

	while (SSH_OK  != (rc = ssh_channel_open_session(channel)))
	{
		if (SSH_ERROR == rc)
		{
			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot establish generic session channel"));
			goto channel_free;
		}
		else if (SSH_AGAIN != rc)
		{
			zabbix_log(LOG_LEVEL_DEBUG, "ssh_channel_open_session: unexpected return code %d", rc);
			break;
		}
	}

	/* request a shell on a channel and execute command */
	dos2unix(item->params);	/* CR+LF (Windows) => LF (Unix) */
	while (SSH_OK  != (rc = ssh_channel_request_exec(channel, item->params)))
	{
		if (SSH_ERROR == rc)
		{
			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot request a shell"));
			goto channel_free;
		}
		else if (SSH_AGAIN != rc)
		{
			zabbix_log(LOG_LEVEL_DEBUG, "ssh_channel_open_session: unexpected return code %d", rc);
			break;
		}
	}

	for (;;)
	{
		do
		{
			if (0 < (rc = ssh_channel_read(channel, buf, sizeof(buf), 0)))
			{
				sz = (size_t)rc;
				if (sz > (size_t)(MAX_BUFFER_LEN - (bytecount + 1)))
					sz = (size_t)(MAX_BUFFER_LEN - (bytecount + 1));
				if (0 == sz)
					continue;

				memcpy(buffer + bytecount, buf, sz);
				bytecount += (int)sz;
			}

		}
		while (rc > 0);

		/* this is due to blocking that would occur otherwise so we loop on
		 * this condition
		 */
		if (SSH_AGAIN == rc)
		{
			continue;
		}
		else if (SSH_ERROR == rc)
		{
			SET_MSG_RESULT(result, zbx_strdup(NULL, "Cannot read data from SSH server"));
			goto channel_close;
		}
		else
			break;
	}

	buffer[bytecount] = '\0';

	output = convert_to_utf8(buffer, (size_t)bytecount, encoding);
	zbx_rtrim(output, ZBX_WHITESPACE);

	if (SUCCEED == set_result_type(result, ITEM_VALUE_TYPE_TEXT, output))
		ret = SYSINFO_RET_OK;

	zbx_free(output);

channel_close:
	ssh_channel_close(channel);
channel_free:
	ssh_channel_free(channel);
session_close:
	if (1 == priv_free)
		ssh_key_free(privkey);
	if (1 == pub_free)
		ssh_key_free(pubkey);
	ssh_disconnect(session);
session_free:
	ssh_free(session);
close:
	zbx_free(publickey);
	zbx_free(privatekey);
	zabbix_log(LOG_LEVEL_DEBUG, "End of %s():%s", __func__, zbx_result_string(ret));

	return ret;
}
#endif

#if defined(HAVE_SSH2) || defined(HAVE_SSH)
int	get_value_ssh(DC_ITEM *item, AGENT_RESULT *result)
{
	AGENT_REQUEST	request;
	int		ret = NOTSUPPORTED;
	const char	*port, *encoding, *dns;

	init_request(&request);

	if (SUCCEED != parse_item_key(item->key, &request))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Invalid item key format."));
		goto out;
	}

	if (0 != strcmp(SSH_RUN_KEY, get_rkey(&request)))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Unsupported item key for this item type."));
		goto out;
	}

	if (4 < get_rparams_num(&request))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Too many parameters."));
		goto out;
	}

	if (NULL != (dns = get_rparam(&request, 1)) && '\0' != *dns)
	{
		strscpy(item->interface.dns_orig, dns);
		item->interface.addr = item->interface.dns_orig;
	}

	if (NULL != (port = get_rparam(&request, 2)) && '\0' != *port)
	{
		if (FAIL == is_ushort(port, &item->interface.port))
		{
			SET_MSG_RESULT(result, zbx_strdup(NULL, "Invalid third parameter."));
			goto out;
		}
	}
	else
		item->interface.port = ZBX_DEFAULT_SSH_PORT;

	encoding = get_rparam(&request, 3);

	ret = ssh_run(item, result, ZBX_NULL2EMPTY_STR(encoding));
out:
	free_request(&request);

	return ret;
}
#endif	/* defined(HAVE_SSH2) || defined(HAVE_SSH) */

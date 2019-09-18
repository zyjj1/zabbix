/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
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

package com.zabbix.gateway;

import java.util.Set;
import java.util.HashMap;
import java.util.Map;
import java.util.HashSet;
import java.util.ArrayList;

import javax.management.AttributeList;

import javax.management.InstanceNotFoundException;
import javax.management.MBeanAttributeInfo;
import javax.management.MBeanServerConnection;
import javax.management.ObjectName;
import javax.management.openmbean.CompositeData;
import javax.management.openmbean.TabularDataSupport;
import javax.management.remote.JMXConnector;
import javax.management.remote.JMXServiceURL;

import org.json.*;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

class JMXItemChecker extends ItemChecker
{
	private static final Logger logger = LoggerFactory.getLogger(JMXItemChecker.class);

	private JMXServiceURL url;
	private JMXConnector jmxc;
	private MBeanServerConnection mbsc;

	private String username;
	private String password;

	static final int DISCOVERY_MODE_ATTRIBUTES = 0;
	static final int DISCOVERY_MODE_BEANS = 1;

	JMXItemChecker(JSONObject request) throws ZabbixException
	{
		super(request);

		String jmx_endpoint;

		try
		{
			jmx_endpoint = request.getString(JSON_TAG_JMX_ENDPOINT);
		}
		catch (Exception e)
		{
			throw new ZabbixException(e);
		}

		try
		{
			url = new JMXServiceURL(jmx_endpoint);
			jmxc = null;
			mbsc = null;

			username = request.optString(JSON_TAG_USERNAME, null);
			password = request.optString(JSON_TAG_PASSWORD, null);
		}
		catch (Exception e)
		{
			throw new ZabbixException("%s: %s", e, jmx_endpoint);
		}
	}

	@Override
	protected JSONArray getValues() throws ZabbixException
	{
		JSONArray values = new JSONArray();

		try
		{
			HashMap<String, String[]> env = null;
			Map<String, String> dataObjectMap = new HashMap<String, String>();
			Set<String> processedKeys = new HashSet<String>();

			if (null != username && null != password)
			{
				env = new HashMap<String, String[]>();
				env.put(JMXConnector.CREDENTIALS, new String[] {username, password});
			}

			jmxc = ZabbixJMXConnectorFactory.connect(url, env);
			mbsc = jmxc.getMBeanServerConnection();

			for (String key : keys)
			{
				ZabbixItem item = new ZabbixItem(key);

				if (item.getKeyId().equals("jmx"))
				{
					if (2 != item.getArgumentCount())
						throw new ZabbixException("required key format: jmx[<object name>,<attribute name>]");

					String objectName = item.getArgument(1);

					// keep track of keys that have been already processed
					if (processedKeys.contains(objectName))
						continue;

					processedKeys.add(objectName);

					ArrayList<String> attributeList = new ArrayList<String>();

					for (String key2 : keys)
					{
						ZabbixItem item2 = new ZabbixItem(key2);

						if (!item2.getKeyId().equals("jmx"))
							continue;

						if (objectName.equals(item2.getArgument(1)))
							attributeList.add(item2.getArgument(2));
					}

					String[] attributeValuesArray = (getAttributeValues(objectName, attributeList.toArray(new String[0])));

					ArrayList<String> attributeValues = new ArrayList<String>();

					for (String attributeValue : attributeValuesArray)
						attributeValues.add(attributeValue);

					for (int i = 0; i < attributeList.size(); i++)
					{
						for (String key2 : keys)
						{
							ZabbixItem item2 = new ZabbixItem(key2);

							if (!item2.getKeyId().equals("jmx"))
								continue;

							if (objectName.equals(item2.getArgument(1)) && item2.getArgument(2).equals(attributeList.get(i)))
							{
								dataObjectMap.put(key2, attributeValues.get(i));
								break;
							}
						}

					}
				}
				else if (item.getKeyId().equals("jmx.discovery"))
				{
					dataObjectMap.put(key, getDiscoveryValue(item));
				}
				else
					throw new ZabbixException("key ID '%s' is not supported", item.getKeyId());
			}

			for (String key : keys)
			{
				JSONObject value = new JSONObject();

				value.put(JSON_TAG_VALUE, dataObjectMap.get(key));
				values.put(value);
			}
		}
		catch (SecurityException e1)
		{
			JSONObject value = new JSONObject();

			logger.warn("cannot process keys '{}': {}: {}", new Object[] {keys, ZabbixException.getRootCauseMessage(e1), url});
			logger.debug("error caused by", e1);

			try
			{
				value.put(JSON_TAG_ERROR, ZabbixException.getRootCauseMessage(e1));
			}
			catch (JSONException e2)
			{
				Object[] logInfo = {JSON_TAG_ERROR, e1.getMessage(), ZabbixException.getRootCauseMessage(e2)};
				logger.warn("cannot add JSON attribute '{}' with message '{}': {}", logInfo);
				logger.debug("error caused by", e2);
			}

			for (int i = 0; i < keys.size(); i++)
				values.put(value);
		}
		catch (Exception e)
		{
			throw new ZabbixException("%s: %s", ZabbixException.getRootCauseMessage(e), url);
		}
		finally
		{
			try { if (null != jmxc) jmxc.close(); } catch (java.io.IOException exception) { }

			jmxc = null;
			mbsc = null;
		}

		return values;
	}

	private String[] getAttributeValues(String objectNameStr, String[] attributeArray) throws Exception
	{
		String realAttributeName;
		String fieldNames;
		ArrayList<String> attributeValues = new ArrayList<String>();
		ArrayList<String> attributeListSimple = new ArrayList<String>();
		AttributeList attributes;

		// Attribute name and composite data field names are separated by dots. On the other hand the
		// name may contain a dot too. In this case user needs to escape it with a backslash. Also the
		// backslash symbols in the name must be escaped. So a real separator is unescaped dot and
		// separatorIndex() is used to locate it.

		for (int i = 0; i < attributeArray.length; i++)
		{
			realAttributeName = attributeArray[i];
			int sep = HelperFunctionChest.separatorIndex(realAttributeName);

			if (-1 != sep)
				realAttributeName = realAttributeName.substring(0, sep);

			// Create a list of atributes without composite data. Method getAttributes() retrievs all
			// composite data values for an attribute.

			if (!attributeListSimple.contains(realAttributeName))
				attributeListSimple.add(realAttributeName);
		}

		try
		{
			ObjectName objectName = new ObjectName(objectNameStr);
			String[] attributeListSimpleStr = attributeListSimple.toArray(new String[0]);
			attributes = mbsc.getAttributes(objectName, attributeListSimpleStr);
		}
		catch (InstanceNotFoundException e)
		{
			throw new ZabbixException("the MBean '%s' is not registered in the MBean server", objectNameStr);
		}

		for (int i = 0; i < attributeArray.length; i++)
		{
			realAttributeName = attributeArray[i];
			fieldNames = "";
			int sep = HelperFunctionChest.separatorIndex(attributeArray[i]);

			if (-1 != sep)
			{
				realAttributeName = attributeArray[i].substring(0, sep);
				fieldNames = attributeArray[i].substring(sep + 1);
			}

			for (javax.management.Attribute attribute : attributes.asList())
			{
				if (attribute.getName().equals(realAttributeName))
				{
					try
					{
						attributeValues.add(getPrimitiveAttributeValue(attribute.getValue(), fieldNames));
						break;
					}
					catch (InstanceNotFoundException e)
					{
						throw new ZabbixException("Object or attribute not found.");
					}
				}
			}
		}

		return attributeValues.toArray(new String[0]);
	}

	private String getPrimitiveAttributeValue(Object dataObject, String fieldNames) throws Exception
	{
		logger.trace("drilling down with data object '{}' and field names '{}'", dataObject, fieldNames);

		if (null == dataObject)
			throw new ZabbixException("data object is null");

		if (fieldNames.equals(""))
		{
			try
			{
				if (isPrimitiveAttributeType(dataObject))
					return dataObject.toString();
				else
					throw new NoSuchMethodException();
			}
			catch (NoSuchMethodException e)
			{
				throw new ZabbixException("Data object type cannot be converted to string.");
			}
		}

		if (dataObject instanceof CompositeData)
		{
			logger.trace("'{}' contains composite data", dataObject);

			CompositeData comp = (CompositeData)dataObject;

			String dataObjectName;
			String newFieldNames = "";

			int sep = HelperFunctionChest.separatorIndex(fieldNames);

			if (-1 != sep)
			{
				dataObjectName = fieldNames.substring(0, sep);
				newFieldNames = fieldNames.substring(sep + 1);
			}
			else
				dataObjectName = fieldNames;

			// unescape possible dots or backslashes that were escaped by user
			dataObjectName = HelperFunctionChest.unescapeUserInput(dataObjectName);

			return getPrimitiveAttributeValue(comp.get(dataObjectName), newFieldNames);
		}
		else
			throw new ZabbixException("unsupported data object type along the path: %s", dataObject.getClass());
	}

	private String getDiscoveryValue(ZabbixItem item) throws Exception
	{
		int argumentCount = item.getArgumentCount();

		if (2 < argumentCount)
			throw new ZabbixException("required key format: jmx.discovery[<discovery mode>,<object name>]");

		JSONArray counters = new JSONArray();
		ObjectName filter = (2 == argumentCount) ? new ObjectName(item.getArgument(2)) : null;

		int mode = DISCOVERY_MODE_ATTRIBUTES;
		if (0 != argumentCount)
		{
			String modeName = item.getArgument(1);

			if (modeName.equals("beans"))
				mode = DISCOVERY_MODE_BEANS;
			else if (!modeName.equals("attributes"))
				throw new ZabbixException("invalid discovery mode: " + modeName);
		}

		for (ObjectName name : mbsc.queryNames(filter, null))
		{
			logger.trace("discovered object '{}'", name);

			if (DISCOVERY_MODE_ATTRIBUTES == mode)
				discoverAttributes(counters, name);
			else
				discoverBeans(counters, name);
		}

		JSONObject mapping = new JSONObject();
		mapping.put(ItemChecker.JSON_TAG_DATA, counters);
		return mapping.toString();
	}

	private void discoverAttributes(JSONArray counters, ObjectName name) throws Exception
	{
		Map<String, Object> values = new HashMap<String, Object>();

		MBeanAttributeInfo[] attributeArray = mbsc.getMBeanInfo(name).getAttributes();
		int i = 0;
		String[] attributeNames = new String[attributeArray.length];

		for (MBeanAttributeInfo attrInfo : attributeArray)
			attributeNames[i++] = attrInfo.getName();

		AttributeList attributes = mbsc.getAttributes(name, attributeNames);

		for (javax.management.Attribute attribute : attributes.asList())
		{
			Object value = attribute.getValue();
			values.put(attribute.getName(), value);
		}

		for (MBeanAttributeInfo attrInfo : attributeArray)
		{
			logger.trace("discovered attribute '{}'", attrInfo.getName());

			if (!attrInfo.isReadable())
			{
				logger.trace("attribute not readable, skipping");
				continue;
			}

			if (null == values.get(attrInfo.getName()))
			{
				logger.trace("cannot retrieve attribute value, skipping");
				continue;
			}

			try
			{
				logger.trace("looking for attributes of primitive types");
				String descr = (attrInfo.getName().equals(attrInfo.getDescription()) ? null : attrInfo.getDescription());
				findPrimitiveAttributes(counters, name, descr, attrInfo.getName(), values.get(attrInfo.getName()));
			}
			catch (Exception e)
			{
				Object[] logInfo = {name, attrInfo.getName(), ZabbixException.getRootCauseMessage(e)};
				logger.warn("attribute processing '{},{}' failed: {}", logInfo);
				logger.debug("error caused by", e);
			}
		}
	}

	private void discoverBeans(JSONArray counters, ObjectName name)
	{
		try
		{
			HashSet<String> properties = new HashSet<String>();
			JSONObject counter = new JSONObject();

			// Default properties are added.
			counter.put("{#JMXOBJ}", name);
			counter.put("{#JMXDOMAIN}", name.getDomain());
			properties.add("OBJ");
			properties.add("DOMAIN");

			for (Map.Entry<String, String> property : name.getKeyPropertyList().entrySet())
			{
				String key = property.getKey().toUpperCase();

				// Property key should only contain valid characters and should not be already added to attribute list.
				if (key.matches("^[A-Z0-9_\\.]+$") && !properties.contains(key))
				{
					counter.put("{#JMX" + key + "}" , property.getValue());
					properties.add(key);
				}
				else
					logger.trace("bean '{}' property '{}' was ignored", name, property.getKey());
			}

			counters.put(counter);
		}
		catch (Exception e)
		{
			logger.warn("bean processing '{}' failed: {}", name, ZabbixException.getRootCauseMessage(e));
			logger.debug("error caused by", e);
		}
	}

	private void findPrimitiveAttributes(JSONArray counters, ObjectName name, String descr, String attrPath, Object attribute) throws NoSuchMethodException, JSONException
	{
		logger.trace("drilling down with attribute path '{}'", attrPath);

		if (isPrimitiveAttributeType(attribute))
		{
			logger.trace("found attribute of a primitive type: {}", attribute.getClass());

			JSONObject counter = new JSONObject();

			counter.put("{#JMXDESC}", null == descr ? name + "," + attrPath : descr);
			counter.put("{#JMXOBJ}", name);
			counter.put("{#JMXATTR}", attrPath);
			counter.put("{#JMXTYPE}", attribute.getClass().getName());
			counter.put("{#JMXVALUE}", attribute.toString());

			counters.put(counter);
		}
		else if (attribute instanceof CompositeData)
		{
			logger.trace("found attribute of a composite type: {}", attribute.getClass());

			CompositeData comp = (CompositeData)attribute;

			for (String key : comp.getCompositeType().keySet())
				findPrimitiveAttributes(counters, name, descr, attrPath + "." + key, comp.get(key));
		}
		else if (attribute instanceof TabularDataSupport || attribute.getClass().isArray())
		{
			logger.trace("found attribute of a known, unsupported type: {}", attribute.getClass());
		}
		else
			logger.trace("found attribute of an unknown, unsupported type: {}", attribute.getClass());
	}

	private boolean isPrimitiveAttributeType(Object obj) throws NoSuchMethodException
	{
		Class<?>[] clazzez = {Boolean.class, Character.class, Byte.class, Short.class, Integer.class, Long.class,
			Float.class, Double.class, String.class, java.math.BigDecimal.class, java.math.BigInteger.class,
			java.util.Date.class, javax.management.ObjectName.class, java.util.concurrent.atomic.AtomicBoolean.class,
			java.util.concurrent.atomic.AtomicInteger.class, java.util.concurrent.atomic.AtomicLong.class};

		// check if the type is either primitive or overrides toString()
		return HelperFunctionChest.arrayContains(clazzez, obj.getClass()) ||
				(!(obj instanceof CompositeData)) && (!(obj instanceof TabularDataSupport)) &&
				(obj.getClass().getMethod("toString").getDeclaringClass() != Object.class);
	}
}

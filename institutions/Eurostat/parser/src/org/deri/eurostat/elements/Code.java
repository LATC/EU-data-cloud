package org.deri.eurostat.elements;

import java.util.HashMap;

public class Code {

	protected String value;
	protected HashMap<String, String> hshDescription;
	
	public Code()
	{
		this.value = null;
		this.hshDescription = null;
	}
	
	public Code(String value, HashMap<String,String> hshDescription)
	{
		this.value = value;
		this.hshDescription = hshDescription;
	}

	public String getValue() {
		return value;
	}

	public void setValue(String value) {
		this.value = value;
	}

	public HashMap<String,String> gethshDescription()
	{
		return hshDescription;
	}
	
	public void setName(HashMap<String,String> hshDescription)
	{
		this.hshDescription = hshDescription;
	}
	
}

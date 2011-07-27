package org.deri.eurostat.elements;

import java.util.HashMap;

public class Concept {

	protected String id;
	protected HashMap<String, String> hshName;

	public Concept()
	{
		this.id = null;
		this.hshName = new HashMap<String, String>();
	}
	
	public Concept(String id, HashMap<String,String> hshName)
	{
		this.id = id;
		this.hshName = hshName;
	}
	
	public String getId()
	{
		return id;
	}
	
	public void setId(String id)
	{
		this.id = id;
	}
	
	public HashMap<String,String> gethshName()
	{
		return hshName;
	}
	
	public void setName(HashMap<String,String> hshName)
	{
		this.hshName = hshName;
	}
}

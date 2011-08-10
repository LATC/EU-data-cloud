package org.deri.eurostat.elements;

import java.util.ArrayList;
import java.util.HashMap;

public class CodeList {

	protected String id;
	protected String agencyID;
	protected String isFinal;
	protected HashMap<String, String> hshName;
	protected ArrayList<Code> lstCodes;
	
	public CodeList()
	{
		this.id = null;
		this.agencyID = null;
		this.isFinal = null;
		this.hshName = null;
		this.lstCodes = new ArrayList<Code>();
	}
	
	public CodeList(String id, String agencyID, String isFinal, HashMap<String,String> hshName, ArrayList<Code> lstCode)
	{
		this.id = id;
		this.agencyID = agencyID;
		this.isFinal = isFinal;
		this.hshName = hshName;
		this.lstCodes = lstCode;
	}
	
	public String getId() {
		return id;
	}
	
	public void setId(String id) {
		this.id = id;
	}
	
	public String getAgencyID() {
		return agencyID;
	}
	
	public void setAgencyID(String agencyID) {
		this.agencyID = agencyID;
	}
	
	public String getIsFinal() {
		return isFinal;
	}
	
	public void setIsFinal(String isFinal) {
		this.isFinal = isFinal;
	}
	
	public HashMap<String,String> gethshName()
	{
		return hshName;
	}
	
	public void setName(HashMap<String,String> hshName)
	{
		this.hshName = hshName;
	}
	
	public ArrayList<Code> getCode() {
		return lstCodes;
	}
	
	public void setComments(ArrayList<Code> code) {
		this.lstCodes = code;
	}

}

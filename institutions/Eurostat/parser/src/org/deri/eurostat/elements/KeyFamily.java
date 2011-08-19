package org.deri.eurostat.elements;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class KeyFamily {

	protected String id;
	protected String agencyID;
	protected String isFinal;
	protected String externalReference;
	protected String name;
	
	public KeyFamily()
	{
		this.id = null;
		this.agencyID = null;
		this.isFinal = null;
		this.name = null;
		this.externalReference = null;
	}
	
	public KeyFamily(String id, String agencyID, String isFinal, String name, String reference)
	{
		this.id = id;
		this.agencyID = agencyID;
		this.isFinal = isFinal;
		this.name = name;
		this.externalReference = reference;
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
	
	public String getName() {
		return name;
	}
	
	public void setName(String name) {
		this.name = name;
	}

	public String getExternalReference() {
		return externalReference;
	}
	
	public void setExternalReference(String reference) {
		this.externalReference = reference;
	}

}

package org.deri.eurostat.elements;

/**
 * 
 * @author Aftab Iqbal
 *
 */

public class Dimension {

	protected String conceptSchemeRef;
	protected String conceptRef;
	protected String codeList;
	protected String dataType;
	
	public Dimension()
	{
		this.conceptRef = null;
		this.conceptSchemeRef = null;
		this.codeList = null;
		this.dataType = null;
	}
	
	public Dimension(String conceptRef, String conceptSchemeRef, String codeList, String type)
	{
		this.conceptRef = conceptRef;
		this.conceptSchemeRef = conceptSchemeRef;
		this.codeList = codeList;
		this.dataType = type;
	}
	
	public String getConceptSchemeRef() {
		return conceptSchemeRef;
	}
	
	public void setConceptSchemeRef(String conceptSchemeRef) {
		this.conceptSchemeRef = conceptSchemeRef;
	}
	
	public String getConceptRef() {
		return conceptRef;
	}
	
	public void setConceptRef(String conceptRef) {
		this.conceptRef = conceptRef;
	}
	
	public String getCodeList() {
		return codeList;
	}
	
	public void setCodeList(String codeList) {
		this.codeList = codeList;
	}
	
	public String getDataType() {
		return dataType;
	}
	
	public void setDataType(String type) {
		this.dataType = type;
	}
	
}

package org.deri.eurostat.elements;

public class Measure {

	protected String conceptSchemeRef;
	protected String conceptRef;
	protected String dataType;
	protected String codeList;
	
	public Measure()
	{
		this.conceptRef = null;
		this.conceptSchemeRef = null;
		this.codeList = null;
		this.dataType = null;
	}
	
	public Measure(String conceptRef, String conceptSchemeRef,String codeList, String dataType)
	{
		this.conceptRef = conceptRef;
		this.conceptSchemeRef = conceptSchemeRef;
		this.codeList = codeList;
		this.dataType = dataType;
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
	
	public void setDataType(String dataType) {
		this.dataType = dataType;
	}
	
}

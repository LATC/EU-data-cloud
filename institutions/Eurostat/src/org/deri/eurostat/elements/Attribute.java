package org.deri.eurostat.elements;

public class Attribute {

	
	protected String conceptSchemeRef;
	protected String conceptRef;
	protected String codeList;
	protected String attachmentLevel;
	protected String assignmentStatus;
	protected String dataType;
	
	public Attribute()
	{
		this.conceptRef = null;
		this.conceptSchemeRef = null;
		this.codeList = null;
		this.attachmentLevel = null;
		this.assignmentStatus = null;
		this.dataType = null;
	}
	
	public Attribute(String conceptRef, String conceptSchemeRef, String codeList, String attachmentLevel, String assignmentStatus, String dataType)
	{
		this.conceptRef = conceptRef;
		this.conceptSchemeRef = conceptSchemeRef;
		this.codeList = codeList;
		this.attachmentLevel = attachmentLevel;
		this.assignmentStatus = assignmentStatus;
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
	
	public String getAttachmentLevel() {
		return attachmentLevel;
	}
	
	public void setAttachmentLevel(String attachmentLevel) {
		this.attachmentLevel = attachmentLevel;
	}
	
	public String getAssignmentStatus() {
		return assignmentStatus;
	}
	
	public void setAssignmentStatus(String assignmentStatus) {
		this.assignmentStatus = assignmentStatus;
	}
	
	public String getDataType() {
		return dataType;
	}
	
	public void setDataType(String dataType) {
		this.dataType = dataType;
	}
		
}

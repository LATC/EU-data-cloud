/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

package com.deri.latc.dto;

import org.apache.commons.httpclient.URIException;
import org.apache.commons.httpclient.util.URIUtil;

/**
 *
 * @author jamnas
 */
public class VoidInfoDto  {



    public VoidInfoDto() {

    }
    private int statItem=-1;

    /**
     * Get the value of statItem
     *
     * @return the value of statItem
     */
    public int getStatItem() {
        return statItem;
    }

    /**
     * Set the value of statItem
     *
     * @param statItem new value of statItem
     */
    public void setStatItem(int statItem) {
        this.statItem = statItem;
    }

   private String linkPredicate=null;

    /**
     * Get the value of linkPredicate
     *
     * @return the value of linkPredicate
     */
    public String getLinkPredicate() {
        return linkPredicate;
    }

    /**
     * Set the value of linkPredicate
     *
     * @param linkPredicate new value of linkPredicate
     */
    public void setLinkPredicate(String linkPredicate) {
        this.linkPredicate = linkPredicate;
    }

    private String globalPrefixes=null;

    /**
     * Get the value of globalPrefixes
     *
     * @return the value of globalPrefixes
     */
    public String getGlobalPrefixes() {
        return globalPrefixes;
    }

    /**
     * Set the value of globalPrefixes
     *
     * @param globalPrefixes new value of globalPrefixes
     */
    public void setGlobalPrefixes(String globalPrefixes) {
        this.globalPrefixes = globalPrefixes;
    }

    private String sourceSparqlEndpoint=null;

    /**
     * Get the value of sourceSparqlEndpoint
     *
     * @return the value of sourceSparqlEndpoint
     */
    public String getSourceSparqlEndpoint() {
        return sourceSparqlEndpoint;
    }

    /**
     * Set the value of sourceSparqlEndpoint
     *
     * @param sourceSparqlEndpoint new value of sourceSparqlEndpoint
     */
    public void setSourceSparqlEndpoint(String sourceSparqlEndpoint) {
        this.sourceSparqlEndpoint = sourceSparqlEndpoint;
    }
    
    
    private String sourceUriLookupEndpoint=null;
    
    /**
     * Get the value of SourceUriLookupEndpoint
     *
     * @return the value of sourceSparqlEndpoint
     */
    public String getSourceUriLookupEndpoint() {
        return sourceUriLookupEndpoint;
    }

    /**
     * Set the value of SourceUriLookupEndpoint
     *
     * @param sourceUriLookupEndpoint new value of sourceUriLookupEndpoint
     */
    public void setSourceUriLookupEndpoint(String sourceUriLookupEndpoint) {
        this.sourceUriLookupEndpoint = sourceUriLookupEndpoint;
    }
    

    private String targetSparqlEndpoint=null;

    /**
     * Get the value of targetSparqlEndpoint
     *
     * @return the value of targetSparqlEndpoint
     */
    public String getTargetSparqlEndpoint() {
        return targetSparqlEndpoint;
    }

    /**
     * Set the value of targetSparqlEndpoint
     *
     * @param targetSparqlEndpoint new value of targetSparqlEndpoint
     */
    public void setTargetSparqlEndpoint(String targetSparqlEndpoint) {
        this.targetSparqlEndpoint = targetSparqlEndpoint;
    }
    
    private String targetUriLookupEndpoint=null;

    /**
     * Get the value of targetUriLookupEndpoint
     *
     * @return the value of targetUriLookupEndpoint
     */
    public String getTargetUriLookupEndpoint() {
        return targetUriLookupEndpoint;
    }

    /**
     * Set the value of targetUriLookupEndpoint
     *
     * @param targetUriLookupEndpoint new value of targetUriLookuEndpoint
     */
    public void setTargetUriLookupEndpoint(String targetUriLookupEndpoint) {
        this.targetUriLookupEndpoint = targetUriLookupEndpoint;
    }
    
    
    private String sourceDatasetName=null;

    /**
     * Get the value of sourceDatasetName
     *
     * @return the value of sourceDatasetName
     */
    public String getSourceDatasetName() {
        return sourceDatasetName;
    }

    /**
     * Set the value of sourceDatasetName
     *
     * @param sourceDatasetName new value of sourceDatasetName
     */
    public void setSourceDatasetName(String sourceDatasetName) {
        this.sourceDatasetName = sourceDatasetName;
    }
    private String targetDatasetName=null;

    /**
     * Get the value of targetDatasetName
     *
     * @return the value of targetDatasetName
     */
    public String getTargetDatasetName() {
        return targetDatasetName;
    }

    /**
     * Set the value of targetDatasetName
     *
     * @param targetDatasetName new value of targetDatasetName
     */
    public void setTargetDatasetName(String targetDatasetName) {
        this.targetDatasetName = targetDatasetName;
    }
    private String thirdPartyInterlinking=null;

    /**
     * Get the value of thirdPartyInterlinking
     *
     * @return the value of thirdPartyInterlinking
     */
    public String getThirdPartyInterlinking() {
        return thirdPartyInterlinking;
    }

    /**
     * Set the value of thirdPartyInterlinking
     *
     * @param thirdPartyInterlinking new value of thirdPartyInterlinking
     */
    public void setThirdPartyInterlinking(String thirdPartyInterlinking) {
        this.thirdPartyInterlinking = thirdPartyInterlinking;
    }
    private String remarks=null;

    /**
     * Get the value of remarks
     *
     * @return the value of remarks
     */
    public String getRemarks() {
        return remarks;
    }

    /**
     * Set the value of remarks
     *
     * @param remarks new value of remarks
     */
    public void setRemarks(String remarks) {
        this.remarks = remarks;
    }
    private String spec=null;

    /**
     * Get the value of dataDump
     *
     * @return the value of dataDump
     */
    public String getSpec() {
        return spec;
    }

    /**
     * Set the value of dataDump
     *
     * @param dataDump new value of dataDump
     */
    public void setSpec(String speclink) {
        this.spec = this.encodeURI(speclink);
    }

    private String SilkSpecAPIResource=null;

    /**
     * Get the value of dataDump
     *
     * @return the value of dataDump
     */
    public String getSilkSpecAPIResource() {
        return SilkSpecAPIResource;
    }

    /**
     * Set the value of dataDump
     *
     * @param dataDump new value of dataDump
     */
    public void setSilkSpecAPIResource(String speclink) {
        this.SilkSpecAPIResource = this.encodeURI(speclink);
    }
    
    private String dataDump=null;

    /**
     * Get the value of dataDump
     *
     * @return the value of dataDump
     */
    public String getDataDump() {
        return dataDump;
    }

    /**
     * Set the value of dataDump
     *
     * @param dataDump new value of dataDump
     */
    public void setDataDump(String dataDump) {
        this.dataDump = this.encodeURI(dataDump);
    }

    private String ID=null;

    /**
     * Get the value of dataDump
     *
     * @return the value of dataDump
     */
    public String getID() {
        return ID;
    }

    /**
     * Set the value of ID
     *
     * @param ID new value of ID
     */
    public void setID(String ID) {
        this.ID = ID;
    }
    
    private String Title=null;

    /**
     * Get the value of dataDump
     *
     * @return the value of dataDump
     */
    public String getTitle() {
        return ID;
    }

    /**
     * Set the value of ID
     *
     * @param ID new value of ID
     */
    public void setTitle(String title) {
        this.Title = title;
    }
    
    private String linkSetCreatedTime=null;
    
    public void setLinkSetCreatedTime (String date)
    {
    	this.linkSetCreatedTime = date;
    }
 
    public String getLinkSetCreatedTime()
    {
    	return this.linkSetCreatedTime;
    }
    
    private String specCreatedTime = null;
   
    public void setSpecCreatedTime (String date)
    {
    	this.specCreatedTime = date;
    }
 
    public String getSpecCreatedTime()
    {
    	return this.specCreatedTime;
    }
    
    private String specModifiedTime = null;
    
    public void setSpecModifiedTime (String date)
    {
    	this.specModifiedTime = date;
    }
 
    public String getSpecModifiedTime()
    {
    	return this.specModifiedTime;
    }
    
    private String specRetrievedTime = null;
    
    public void setSpecRetrievedTime (String date)
    {
    	this.specRetrievedTime = date;
    }
 
    public String getSpecRetrievedTime()
    {
    	return this.specRetrievedTime;
    }
    
 private String specAuthor = null;
    
    public void setSpecAuthor (String author)
    {
    	this.specAuthor = author;
    }
 
    public String getSpecAuthor()
    {
    	return this.specAuthor;
    }
    
    private String encodeURI (String URI)
    {
    	
    	try {
			URI= URIUtil.decode(URI);
		} catch (URIException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return URI;
    }
}

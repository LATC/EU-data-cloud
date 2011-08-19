package com.ontologycentral.estatwrap;

import java.util.ArrayList;
import java.util.List;
import java.util.StringTokenizer;
public class Line {

	List<String> _dim1 = new ArrayList();
	List<String> _cols;

	public Line(String line)
	{
		List cols = new ArrayList();
	
		StringTokenizer st = new StringTokenizer(line, "\t");
		while (st.hasMoreTokens()) {
			cols.add(st.nextToken().trim());
		}
	 
	    String legend = (String)cols.get(0);
	 
	    this._cols = cols.subList(1, cols.size());
	 
	    st = new StringTokenizer(legend, ",");
	    while (st.hasMoreTokens()) {
	       String tok = st.nextToken().trim();
	       this._dim1.add(tok);
	    }
	 }
	 
	 public List<String> getCols() {
	     return this._cols;
	 }
	 
	 public List<String> getDim1() {
	     return this._dim1;
	 }
	 
	 public String toString() {
	     StringBuffer sb = new StringBuffer();
	 
	     sb.append(this._dim1);
	     sb.append(":");
	     sb.append(this._cols);
	 
	     return sb.toString();
	 }

}

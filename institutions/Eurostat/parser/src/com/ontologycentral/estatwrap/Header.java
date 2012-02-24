package com.ontologycentral.estatwrap;

import java.util.ArrayList;
import java.util.List;
import java.util.StringTokenizer;

class Header {
	List<String> _dim1 = new ArrayList<String>();
	String _dim2;
	List<String> _cols;

	public Header(String line) {
		List<String> cols = new ArrayList<String>();
		
		StringTokenizer st = new StringTokenizer(line, "\t");
		while (st.hasMoreTokens()) {
			cols.add(st.nextToken().trim());
		}

		String legend = (String)cols.get(0);

		_cols = cols.subList(1, cols.size());

		int mark = legend.indexOf("\\");
		String dim1 = legend.substring(0, mark);
		_dim2 = legend.substring(mark + 1);
		st = new StringTokenizer(dim1, ",");
		while (st.hasMoreTokens()) {
			String tok = st.nextToken().trim();
			_dim1.add(tok);
		}
	}

	public List<String> getCols() {
		return _cols;
	}

	public List<String> getDim1() {
		return _dim1;
	}

	public String getDim2() {
		return _dim2;
	}

	public String toString() {
		StringBuffer sb = new StringBuffer();

		sb.append(_dim1);
		sb.append("\\");
		sb.append(_dim2);
		sb.append(":");
		sb.append(_cols);

		return sb.toString();
	}
}

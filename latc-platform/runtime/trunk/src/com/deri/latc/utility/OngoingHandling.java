package com.deri.latc.utility;

import java.lang.reflect.Field;
import java.util.Timer;
import java.util.TimerTask;
import java.util.logging.Logger;

import com.deri.latc.dto.VoidInfoDto;

public class OngoingHandling {

	Timer timer;
	 static Process process;
	VoidInfoDto Void;

	  public OngoingHandling(Process p, VoidInfoDto vi) {
	    timer = new Timer();
	    timer.schedule(new RemindTask(), 3*3600 * 1000 ); // 3 hours
	    this.process =p;
	   
	    this.Void = vi;
	  }

	  public void done()
	  {
		  timer.cancel();
		 
	  }
	  class RemindTask extends TimerTask {
	    public void run() {
	    	System.out.println("Time's up!");
	    	try {
				killUnixProcess();
				Void.setStatItem(-2);
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
	    timer.cancel(); 
	    }
	  }
	  
	  public static int getUnixPID() throws Exception
	  {
	      System.out.println(process.getClass().getName());
	      if (process.getClass().getName().equals("java.lang.UNIXProcess"))
	      {
	          Class cl = process.getClass();
	          Field field = cl.getDeclaredField("pid");
	          field.setAccessible(true);
	          Object pidObject = field.get(process);
	          return (Integer) pidObject;
	      } else
	      {
	          throw new IllegalArgumentException("Needs to be a UNIXProcess");
	      }
	  }

	  public static int killUnixProcess() throws Exception
	  {
	      int pid = getUnixPID();
	      return Runtime.getRuntime().exec("kill -9 " + pid).waitFor();
	  }

	public static void main(String[] args) {
		// TODO Auto-generated method stub

	}

}

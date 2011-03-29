package eu.latc.linkqa;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.conf.Configured;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.ByteWritable;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.*;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.input.FileSplit;
import org.apache.hadoop.mapreduce.lib.input.LineRecordReader;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.util.GenericOptionsParser;
import org.apache.hadoop.util.Tool;
import org.apache.hadoop.util.ToolRunner;

import java.io.IOException;
import java.util.HashSet;
import java.util.Set;

//http://developer.yahoo.com/hadoop/tutorial/module5.html
//http://www.mail-archive.com/common-user@hadoop.apache.org/msg00541.html
//http://wiki.apache.org/hadoop/HadoopMapReduce



public class LinkCorrectnessAnalyser extends Configured implements Tool {

    @Override
    public int run(String[] strings) throws Exception {
        //PropertyConfigurator
        //Configuration conf = new Configuration();

        Configuration conf = this.getConf();
        GenericOptionsParser parser = new GenericOptionsParser(this.getConf(), strings);
        //Configuration conf = parser.getConfiguration();

        //String[] otherArgs = new GenericOptionsParser(conf, args).getRemainingArgs();
        String[] otherArgs = parser.getRemainingArgs();
        if (otherArgs.length != 3) {
            System.err.println("Usage: <progname> <in_a> <in_b> <out>");
            System.exit(2);
        }

        Job job = new Job(conf, "eu.latc.linkqa.LinkCorrectnessAnalyser");


        job.setJarByClass(LinkCorrectnessAnalyser.class);
        //job.setMapperClass(IdentityMapper.class);
        //job.setCombinerClass(SourceContainmentCombiner.class);
        //job.setReducerClass(SumReducer.class);

        job.setReducerClass(SourceContainmentCombiner.class);

        job.setMapOutputKeyClass(Text.class);
        job.setMapOutputValueClass(ByteWritable.class);

        //job.set

        job.setOutputKeyClass(Text.class);
        job.setOutputValueClass(LongWritable.class);


        Path fileA = new Path(otherArgs[0]);
        Path fileB = new Path(otherArgs[1]);
        fileA = fileA.makeQualified(fileA.getFileSystem(conf));
        fileB = fileB.makeQualified(fileB.getFileSystem(conf));

        job.getConfiguration().setLong(fileA.toString(), 0);
        job.getConfiguration().setLong(fileB.toString(), 1);

        //System.out.println("ORG CHECK = " + job.getConfiguration().getLong("file:/home/raven/Projects/Current/IntelliJ/latc/latc-platform/linkqa/hdfs/a.txt", 666));

        job.setInputFormatClass(LineSourceInputFormat.class);


        FileInputFormat.addInputPath(job, fileA);
        FileInputFormat.addInputPath(job, fileB);

        //System.out.println("ORG PATH: " + fileA.toString());

        FileOutputFormat.setOutputPath(job, new Path(otherArgs[2]));



        return job.waitForCompletion(true) ? 0 : 1;
    }

    public static class LineSourceInputFormat extends
            FileInputFormat<Text, ByteWritable> {
        @Override
        public RecordReader<Text, ByteWritable> createRecordReader(InputSplit inputSplit, TaskAttemptContext taskAttemptContext) throws IOException, InterruptedException {
            LineSourceRecordReader result = new LineSourceRecordReader();
            result.initialize(inputSplit, taskAttemptContext);
            return result;
        }
    }

    public static class LineSourceRecordReader extends RecordReader<Text, ByteWritable> {
        private LineRecordReader lineReader = new LineRecordReader();
        private FileSplit split;
        private ByteWritable fileId;


        @Override
        public void initialize(InputSplit inputSplit, TaskAttemptContext taskAttemptContext) throws IOException, InterruptedException {
            //Configuration conf = taskAttemptContext.getConfiguration();

            split = (FileSplit) inputSplit;
            String pathName = split.getPath().toString();

            //System.out.println("REC PATH = " + pathName);
            //System.out.println("REC CHECK = " + conf.getLong("file:/home/raven/Projects/Current/IntelliJ/latc/latc-platform/linkqa/hdfs/a.txt", 666));



            byte tmpFileId = (byte)taskAttemptContext.getConfiguration().getLong(pathName, 123);
            //System.out.println("REC FILE ID = " + tmpFileId);

            fileId = new ByteWritable(tmpFileId);

            lineReader.initialize(inputSplit, taskAttemptContext);
        }

        @Override
        public boolean nextKeyValue() throws IOException, InterruptedException {
            return lineReader.nextKeyValue();
        }

        @Override
        public Text getCurrentKey() throws IOException, InterruptedException {
            return lineReader.getCurrentValue();
        }

        @Override
        public ByteWritable getCurrentValue() throws IOException, InterruptedException {
           return fileId;
        }

        public float getProgress() throws IOException {
            return lineReader.getProgress();
        }

        @Override
        public void close() throws IOException {
            lineReader.close();
        }
    }


    /**
     * Counts for each source
     *   . how many keys they contained
     *   . how many of them were distinct,
     *   . how many keys
     */
    public static class SourceContainmentCombiner
            extends Reducer<Text, ByteWritable, Text, LongWritable> {

        private long countA = 0;
        private long countB = 0;
        private long countC = 0; // C = triples (c)ommon to both sources

        @Override
        public void reduce(Text key, Iterable<ByteWritable> values,
                           Context context
        ) throws IOException, InterruptedException {
            Set<Byte> fileIds = new HashSet<Byte>();
            for (ByteWritable val : values) {
                byte fileId = val.get();

                fileIds.add(fileId);
            }

            if(fileIds.contains((byte)0)) { ++countA; }
            if(fileIds.contains((byte)1)) { ++countB; }
            if(fileIds.size() >= 2) { ++countC; }


            /*
            Set<Byte> fileIds = new HashSet<Byte>();
            for (ByteWritable val : values) {
                fileIds.add(val.get());


                //context.write(val, one);
            }

            if(fileIds.size() >= 2) {
                context.write(both, one);
            }
            */
        }


        protected void cleanup(Context context)
                throws java.io.IOException, java.lang.InterruptedException
        {
            //System.out.printf("CLEANUP %d %d %d\n", countA, countB, countC);

            context.write(new Text("source_a_count_total"), new LongWritable(countA));
            context.write(new Text("source_b_count_total"), new LongWritable(countB));
            context.write(new Text("source_c_count_total"), new LongWritable(countC));
        }
    }


    /**
     * Sum up all the keys' corresponding values
     *
     */
    public static class SumReducer
            extends Reducer<Text, LongWritable, Text, LongWritable> {
        private LongWritable result = new LongWritable();

        private final static LongWritable one = new LongWritable(1);
        private final static ByteWritable both = new ByteWritable((byte)66);

        @Override
        public void reduce(Text key, Iterable<LongWritable> values,
                           Context context
        ) throws IOException, InterruptedException {
            long sum = 0;
            for (LongWritable val : values) {
                sum += val.get();
            }

            context.write(key, new LongWritable(sum));
        }
    }

    public static void main(String[] args) throws Exception {
        int ret = ToolRunner.run(new LinkCorrectnessAnalyser(), args);
        System.exit(ret);
    }



    /**
     * Simply writes each key-value pair it receives out again.
     * This results in a grouping of all keys' values.
     *
     * Seems to not be required, as this is done by default by Hadoop if no mapper is provided
     *
     */
    public static class IdentityMapper
            extends Mapper<Text, ByteWritable, Text, ByteWritable>
    {
        @Override
        public void map(Text key, ByteWritable value, Context context)
                throws IOException, InterruptedException
        {
            context.write(key, value);
        }
    }
}
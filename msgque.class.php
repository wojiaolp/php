<?php
/**
 * file msgQueue.class.php
 * @abstract CMS 组消息队列基础类，可直接用，也可按业务扩展
 * @date 2015/04/21
 * @author CMS.wangcun
 * @usage 
 *   $queueName  = "test";
 *   $jobclass   = "Test_Job";
 *   $msgQueue   = new msgQueue(&$jobclass, &$queueName, &$redis);
 *   $msgQueue->add2Queue($data);
 */
class msgQueue
{
    private $redis;
    private $queueList;
    private $res;
    private $queueJobName;
    private $job_class;
    public function __construct(&$jobclass, &$queue_name, &$redis)
    {
        $this->job_class= $jobclass;
        $this->queueList= "resque:queues";        
        $this->setRedis($redis);
        $this->setQueueJobName($queue_name);
        $this->setQueueList($queue_name);
    }
    protected function setRedis(&$redis)
    {
        $this->redis    = $redis;
    }
    protected function setQueueList($queueName)
    {
        if (!empty($queueName) && !empty($this->redis)) {
            $this->redis->sadd($this->queueList, $queueName);
        }
    }
    protected function setQueueJobName($queueName)
    {
        $this->queueJobName = "resque:queue:".$queueName;
    }
    protected function getQueueJobName ()
    {
        return $this->queueJobName;
    }
    protected function callPre()
    {
    }
    protected function callEnd()
    {
    }
    protected function initJobData($data)
    {
        if (!empty($data)) {
            $this->res          = array();
            $this->res['class'] = $this->job_class;
            $this->res['id']    = md5(uniqid(''));            
            $this->res['args'][0]['array'] = msgQueue::encoding($data);
        }
        return $this->res;
    }
    
    public function add2Queue($data)
    {
        $this->callPre();
        $data           = $this->initJobData($data);
        $data['args'][0]['time']= time() .".". rand(0,9999999);
        $data           = json_encode($data);
        $status = $this->redis->rPush($this->getQueueJobName(), $data);
        $this->callEnd();
        return $data;
    }
    
    static public function encoding($data, $input='gbk', $out='utf-8')
    {
        if (is_array($data)) {
            foreach($data as $key=>$value) {
                $data[$key] = msgQueue::encoding($value, $input, $out);
            }
            return $data;
        } else {
            return iconv("{$input}", "{$out}//IGNORE", $data);
        }
    }
    static public function object_array($array)
    {
        if(is_object($array))  {
            $array = (array)$array;
        }
        if(is_array($array))  {
            foreach($array as $key=>$value)  {
                $array[$key] = msgQueue::object_array($value);
            }
        }
        return $array;
    }
    static public function array2object($data)
    {
        if (is_array($data)) {  
            $obj = new StdClass();
            foreach ($data as $key => $val) {  
                $obj->$key = msgQueue::array2object($val);
            }  
        } else {
            $obj = $data;
        }
        return $obj;  
    }
}

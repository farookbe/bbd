<?php
/**

*/

class BBB_Meeting
{
    private $_id;
    private $_external_id;
    private $_external_name;

    public $date;
    public $name;

    /**
        Meeting
        @param $mid Meeting Identifier
    */
    function __construct($mid)
    {
        $this->_id = $mid;
        $this->_init();
        $x = array();
        $x[] = $this->_external_id;
        $x[] = $this->_external_name;
        $x = implode('/',$x);
        if (strlen($x) > 1) $this->name = $x;

        // Date
        if (preg_match('/\-(\d+)$/',$this->_id,$m)) {
            $this->date = strftime('%Y-%m-%d %H:%M', intval($m[1]) / 1000);
        }
    }

    function playURI()
    {
        return '/playback/presentation/playback.html?meetingId=' . $this->_id;
    }

    #	A -- Audio
    #	P -- Presentation
    #	V -- Video
    #	D -- Desktop 
    #	E -- Events
    function archiveStat()
    {
        $ret = array(
            'audio' => glob("/var/bigbluebutton/recording/raw/{$this->_id}/audio/*"),
            'video' => glob("/var/bigbluebutton/recording/raw/{$this->_id}/video/*"),
            'slide' => glob("/var/bigbluebutton/recording/raw/{$this->_id}/presentation/*"),
            'share' => glob("/var/bigbluebutton/recording/raw/{$this->_id}/deskshare/*"),
            'event' => glob("/var/bigbluebutton/recording/raw/{$this->_id}/events.xml"),
        );
        return $ret;
    }
    
    function processStat()
    {
        $ret = array();

        $list = array('recorded','archived','sanity');
        foreach ($list as $k) {

            $ret[$k] = array(
                'file' => sprintf('%s/%s/%s.done',BBB::STATUS,$k,$this->_id)
            );

            $ret[$k]['time_alpha'] = filemtime($ret[$k]['file']);
            $ret[$k]['time_omega'] = filectime($ret[$k]['file']);

        }

        return $ret;
    }

    /**
        Returns Information on the Sources
    */
    function sourceStat()
    {
        // echo '<pre>' . print_r(glob("/var/freeswitch/meetings/{$this->_id}-*"),true) . '</pre>';
        // 
        // echo '<h3><i class="icon-facetime-video"></i> Raw Video</h3>';
        // echo '<pre>' . print_r(glob("/usr/share/red5/webapps/video/streams/{$this->_id}"),true) . '</pre>';
        // 
        // echo '<h3> Raw Presentation Slides</h3>';
        // echo '<pre>' . print_r(glob("/var/bigbluebutton/{$this->_id}/{$this->_id}/*"),true) . '</pre>';

        // echo '<h3> Desk Share</h3>';
        // echo '<pre>' . print_r(glob("var/bigbluebutton/deskshare/{$this->_id}"),true) . '</pre>';

        $ret = array(
            'audio' => glob("/var/freeswitch/meetings/{$this->_id}-*"),
            'video' => glob("/usr/share/red5/webapps/video/streams/{$this->_id}/*"),
            'slide' => glob("/var/bigbluebutton/{$this->_id}/{$this->_id}/*"),
            'share' => glob("var/bigbluebutton/deskshare/{$this->_id}"),
        );
        return $ret;
    }

    /**

    */
    private function _init()
    {
        // Look for My Cached Data
        $file = "/var/bigbluebutton/{$this->_id}/bbd-cache.bin";
        if (is_file($file)) {
            $data = unserialize(file_get_contents($file));
            $this->_external_id = $data['id'];
            $this->_external_name = $data['name'];
        }


        $file = "/var/bigbluebutton/recording/raw/{$this->_id}/events.xml";
        if (is_file($file)) {
            $name = array();
            $fh = fopen($file,'r');
            $buf = fread($fh,2048);

            if(preg_match('/ meetingId="(.+?)"/',$buf,$m)) {
                $this->_external_id = $m[1];
            }
            if(preg_match('/ meetingName="(.+?)" /',$buf,$m)) {
                $this->_external_name = $m[1];
            }
            fclose($fh);
        }

    }
}
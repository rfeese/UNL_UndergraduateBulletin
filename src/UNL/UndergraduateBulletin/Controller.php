<?php
class UNL_UndergraduateBulletin_Controller implements UNL_UndergraduateBulletin_PostRunReplacements
{
    /**
     * URL to this controller.
     *
     * @var string
     */
    public static $url = '';
    
    public $output;
    
    public $options = array('view'   => 'index', // The default from the view_map
                            'format' => 'html',  // The default output format
    );
    
    protected $view_map = array(
        'index'         => 'UNL_UndergraduateBulletin_Introduction',
        'majors'        => 'UNL_UndergraduateBulletin_MajorList',
        'major'         => 'UNL_UndergraduateBulletin_Major',
        'courses'       => 'UNL_UndergraduateBulletin_Major',
        'subject'       => 'UNL_UndergraduateBulletin_SubjectArea',
        'subjects'      => 'UNL_UndergraduateBulletin_SubjectAreas',
        'course'        => 'UNL_UndergraduateBulletin_Listing',
        'college'       => 'UNL_UndergraduateBulletin_College',
        'searchcourses' => 'UNL_UndergraduateBulletin_CourseSearch'
        );
    
    protected static $replacement_data = array();
    
    function __construct($options = array())
    {
        $this->options = array_merge($this->options, $options);
        try {
            $this->run();
        } catch(Exception $e) {
            $this->output[] = $e;
        }
    }
    
    function run()
    {
        switch($this->options['format']) {
        case 'partial':
            UNL_UndergraduateBulletin_ClassToTemplateMapper::$output_template[__CLASS__] = 'Controller-partial';
        case 'html':
        default:
            // Standard template works for html.
            
            break;
        }
        if (isset($this->view_map[$this->options['view']])) {
            $this->output[] = new $this->view_map[$this->options['view']]($this->options);
        } else {
            $this->output[] = new Exception('Sorry, that view does not exist.');
        }
    }
    
    public static function setReplacementData($field, $data)
    {
        self::$replacement_data[$field] = $data;
    }
    
    function postRun($data)
    {
        $scanned = new UNL_Templates_Scanner($data);
        
        if (isset(self::$replacement_data['doctitle'])) {
            $data = str_replace($scanned->doctitle,
                                '<title>'.self::$replacement_data['doctitle'].'</title>',
                                $data);
        }
        
        if (isset(self::$replacement_data['head'])) {
            $data = str_replace('</head>', self::$replacement_data['head'].'</head>', $data);
        }

        if (isset(self::$replacement_data['breadcrumbs'])) {
            $data = str_replace($scanned->breadcrumbs,
                                self::$replacement_data['breadcrumbs'],
                                $data);
        }
        
        return $data;
    }
    
    /**
     * Get the URL for the system or a specific object this controller can display.
     *
     * @param mixed $mixed             Optional object to get a URL for.
     * @param array $additional_params Extra parameters to adjust the URL.
     * 
     * @return string
     */
    static function getURL($mixed = null, $additional_params = array())
    {
        $params = array();
         
        $url = self::$url;
        
        if (is_object($mixed)) {
            switch (get_class($mixed)) {
            
            default:
                    
            }
        }
        
        $params = array_merge($params, $additional_params);
        
        $url .= '?';
        
        foreach ($params as $option=>$value) {
            if (!empty($value)) {
                $url .= "&amp;$option=$value";
            }
        }
        
        return trim($url, '?;=');
    }
    
    static function getDataDir()
    {
        return dirname(dirname(dirname(dirname(__FILE__)))).'/data';
    }
}
?>
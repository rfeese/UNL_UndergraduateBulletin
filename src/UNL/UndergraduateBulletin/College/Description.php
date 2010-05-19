<?php
class UNL_UndergraduateBulletin_College_Description
{
    public $college;
    
    public $description;
    
    public $majors = array();
    
    protected static $files = array(
        'Architecture'                              => 'ARCH College.epub/OEBPS/ARCH_College.xhtml',
        'Agricultural Sciences & Natural Resources' => 'CASNR College Page.epub/OEBPS/CASNR_College_Page.xhtml',
        'Arts & Sciences'                           => 'ASC College page.epub/OEBPS/AandS.xhtml',
        'Business Administration'                   => 'CBA College page.epub/OEBPS/CBA_College_page.xhtml',
        'Education & Human Sciences'                => 'CEHS.epub/OEBPS/College_Page_test-CEHS.xhtml',
        'Engineering'                               => 'College of Engineering.epub/OEBPS/College_of_Engineering.xhtml',
        'Fine & Performing Arts'                    => 'FPA College.epub/OEBPS/FPA_College.xhtml',
        'Journalism & Mass Communications'          => 'College of Jour & Mass Comm.epub/OEBPS/College_of_Jour_&_Mass_Comm-2.xhtml',
        'Libraries'                                 => 'LIBR College Page.epub/OEBPS/LIBR_College_page.xhtml',
        'Public Affairs & Community Service'        => 'CPACS College Page.epub/OEBPS/CPACS_College_page.xhtml',
        'Reserve Officers\' Training Corps (ROTC)'  => 'ROTC College Page.epub/OEBPS/ROTC_College_page.xhtml',
    );
    
    protected $_xml;
    
    function __construct(UNL_UndergraduateBulletin_College $college)
    {
        $this->college = $college;

        if (!isset(self::$files[$college->name])) {
            throw new Exception('No description for the "'.$college->name.'" college.');
        }
        $file = 'phar://'.UNL_UndergraduateBulletin_Controller::getDataDir().'/colleges/'.self::$files[$college->name];

        $this->_xml = simplexml_load_string(file_get_contents($file));

        // Fetch all namespaces
        $namespaces = $this->_xml->getNamespaces(true);
        $this->_xml->registerXPathNamespace('default', $namespaces['']);

        // Register the rest with their prefixes
        foreach ($namespaces as $prefix => $ns) {
            $this->_xml->registerXPathNamespace($prefix, $ns);
        }

        $body = $this->_xml->xpath('//default:body');
        
        $this->parseQuickPoints();

        $this->description = UNL_UndergraduateBulletin_EPUB_Utilities::format($body[0]->children()->asXML());
    }
    
    function parseQuickPoints()
    {
        $quickpoints = $this->_xml->xpath('//default:p[@class="quick-points"]');

        while (list( , $quickpoint) = each($quickpoints)) {
            // Handle quickpoint
            if (isset($quickpoint->span)) {
                $point_desc = (string)$quickpoint->span;
            } else {
                $point_desc = (string)$quickpoint;
            }
            if (preg_match('/([A-Z\s]+)+:/', $point_desc, $matches)) {
                $value = trim(str_replace($matches[0], '', (string)$quickpoint));
                switch($matches[1]) {
                    case 'MAJORS':
                        $this->majors = explode(', ', $value);
                        asort($this->majors);
                        break;
                }
            }
        }
    }
    
    function __isset($var)
    {
        switch ($var) {
            case 'admissionRequirements':
                $section_title = 'ADMISSION';
                break;
            case 'bulletinRule':
                $section_title = 'BULLETIN TO USE';
                break;
            case 'other':
                $section_title = 'OTHER';
                break;
            case 'degreeRequirements':
                $section_title = 'COLLEGE DEGREE REQUIREMENTS';
                break;
            case 'aceRequirements':
                $section_title = 'ACE REQUIREMENTS';
                break;
            default:
                return false;
        }
        // first find the content box headings for the major page
        $nodes = $this->_xml->xpath('//default:p[@class="content-box-m-p" and contains(.,"'.$section_title.'")]');
        
        return (bool)count($nodes);
    }
    
    function __get($var)
    {
        switch ($var) {
            case 'admissionRequirements':
                $section_title = 'ADMISSION';
                break;
            case 'bulletinRule':
                $section_title = 'BULLETIN TO USE';
                break;
            case 'other':
                $section_title = 'OTHER';
                break;
            case 'degreeRequirements':
                $section_title = 'COLLEGE DEGREE REQUIREMENTS';
                break;
            case 'aceRequirements':
                $section_title = 'ACE REQUIREMENTS';
                break;
            default:
                throw new Exception('unknown variable!');
        }

        // first find the content box headings for the major page
        $nodes = $this->_xml->xpath('//default:p[@class="content-box-m-p" and contains(.,"'.$section_title.'")]');

        if (!count($nodes)) {
            throw new Exception('No college section '.$var.' found for '.$this->college->name);
        }

        $content = '';
        // now loop through all following siblings until we find the next section
        foreach ($nodes[0]->xpath('following-sibling::*') as $sibling_node) {
            // Check to see if we've captured anything yet.
            if (!empty($content)) {
                // Loop through this node's attributes for the class
                foreach ($sibling_node->attributes() as $attr => $value) {
                    if ($attr == 'class') {
                        switch ($value) {
                            case 'content-box-h-1':
                            case 'content-box-m-p':
                                // We've found the next section, return the content
                                return UNL_UndergraduateBulletin_EPUB_Utilities::format($content);
                        }
                    }
                }
            }
            // Add the raw xml of this sibling to the content we'll return.
            $content .= $sibling_node->asXML().PHP_EOL;
        }
        return UNL_UndergraduateBulletin_EPUB_Utilities::format($content);

    }
    
    function __toString()
    {
        return $this->description;
    }
}
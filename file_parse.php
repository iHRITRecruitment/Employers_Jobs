<?php
/**
 * Created by Mr. Jason A. Mullings.
 * File Name: file_parse.php
 * User: jlmconsulting
 * Date: 17/02/2017
 * Time: 10:44 PM
 */

include 'vendor/autoload.php';

use Html2Text\Html2Text;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Documents\Document;
use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use \NlpTools\Tokenizers\ClassifierBasedTokenizer;
use \NlpTools\Classifiers\ClassifierInterface;
use \NlpTools\Documents\DocumentInterface;


class EndOfSentence implements ClassifierInterface
{
    public function classify(array $classes, DocumentInterface $d) {
        list($token,$before,$after) = $d->getDocumentData();

        $dotcnt = count(explode('.',$token))-1;
        $lastdot = substr($token,-1)=='.';

        if (!$lastdot) // assume that all sentences end in full stops
            return 'O';

        if ($dotcnt>1) // to catch some naive abbreviations U.S.A.
            return 'O';

        return 'EOW';
    }
}

class parseAds
{
    var $tok;

    public function __construct()
    {
        $this->tok = new ClassifierBasedTokenizer(
            new EndOfSentence(),
            new WhitespaceTokenizer()
        );
    }

    /**
     * @param $url
     * @return mixed
     * Access website via CURL
     */
    public function GetRawData($url)
    {

        $ch = curl_init($url);
        // Set the options
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7) AppleWebKit/534.48.3 (KHTML, like Gecko) Version/5.1 Safari/534.48.3');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec ($ch);
       // sleep(0.5);
        // Close the handle and return the data we retrieved
        curl_close($ch);
        return $contents;
    }

    /**
     * @param $text
     * @return array
     * Parse text string via NLP
     */
    private function parseFile($text)
    {
        $this->tok = new WhitespaceAndPunctuationTokenizer();
        return $this->tok->tokenize($text);
    }

    /**
     * @return mixed
     * DB is merely a text file
     */
    public function pullFile()
    {
        $recoveredData = file_get_contents('lib/saveIntersect.txt');
        return unserialize($recoveredData);

    }

    /**
     * @param $share
     * @return array
     * Get percentage of top five results
     */
    private function class_percentage($share)
    {

        $total = array_sum($share);
        if ($share != null)
            if (max($share) > 0)
                $share = array_map(function ($hits) use ($total) {
                    return round($hits / $total, 3);
                }, $share);

        foreach ($share as $key => $value)
            if ($value == 0)
                unset($share[$key]);

        return $share;
    }

    /**
     * @param $array
     * @return mixed
     * Save both old and new data
     */
    private function saveIntersect($array)
    {
        $serializedData = serialize($array);
        file_put_contents('lib/saveIntersect.txt', $serializedData);
        return $array;
    }

    /**
     * @param $array
     * @return array|null
     * Format for Charts.JS
     */
    private function encodeAnalytics($array)
    {
        $arr = null;
        foreach ($this->class_percentage(array_slice($array,0,5)) as $key=>$val) {
            $arr[] = ["y" => $val, "legendText" => $key, "label" => $key];
        }
        return $arr;
    }

    /**
     * @param $array
     * @return array
     * Divide arrary into left and right pie charts
     */
    private function pieAnalytics($array)
    {
        $dataPoints[] = $this->encodeAnalytics($array['current']);

        $dataPoints[] = $this->encodeAnalytics($array['total']);
        return $dataPoints;
    }

    /**
     * @param $string
     * @param $tagname
     * @return mixed
     * Collect everything with these HTML tags
     */
    private function tagData($string, $tagname)
    {
        $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
        preg_match($pattern, $string, $matches);
        return $matches[1];
    }

    /**
     * @param $html
     * @return array
     * @throws \Html2Text\Html2TextException
     * Clear data, convert with Html2Text and format for return
     */
    public function returnStats($html)
    {
        $text =null;
        $Htmltext = new Html2Text();

        foreach ($html["address"] as $site) {

            $body = $Htmltext->convert(htmlentities($this->GetRawData($site), ENT_QUOTES | ENT_IGNORE, "UTF-8"));
            $text .= $this->tagData($body, "body");
        }

        $adjectives = include "lib/adjectives.data.php";
        $repo = array();
        foreach ($this->parseFile($text) as $word) {
            if(in_array($word,$adjectives))
                $repo[] =$word;
        }

        $newArr = array_count_values($repo);
        arsort($newArr);
        $total =$this->pullFile();
        foreach ($newArr as $k=>$value) {
            if(isset($total[$k]))
            $total[$k]+=$value;
            else
                $total[$k]=$value;
        }

        return $this->pieAnalytics(array('current'=>$newArr,'total'=>$this->saveIntersect($total)));

    }
}

/**
 * Get process linked to JS Ajax
 */
if (isset($_GET['data'])) {
    $words = new parseAds();
    $array  =$words->returnStats($_GET['data']);
   print_r(serialize($array));
}
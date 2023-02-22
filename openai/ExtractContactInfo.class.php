<?php


/**
 * Извличане на контактни данни от имейлите
 *
 * @category  bgerp
 * @package   openai
 *
 * @author    Yusein Yuseinov <yyuseinov@gmail.com>
 * @copyright 2006 - 2023 Experta OOD
 * @license   GPL 3
 *
 * @since     v 0.1
 */
class openai_ExtractContactInfo
{
    /**
     * Масив с елементите, които могат да се игнорират
     * @var string[]
     */
    public static $ignoreArr = array('-', 'none', 'N/A', 'Unknown', 'Not Specified');


    /**
     * Връща контактните данни от имейла
     *
     * @param $id
     * @param null|stdClass $cData
     * @param boolean|string $useCache
     *
     * @return false|string
     * @throws core_exception_Expect
     */
    public static function extractEmailData($id, &$cData = null, $useCache = true)
    {
        $rec = email_Incomings::fetchRec($id);

        expect($rec && $rec->emlFile);

        return self::extractEmailDataFromEml($rec->emlFile, $rec->lg, $cData, $useCache);
    }


    /**
     * Връща контактните данни от eml файла
     *
     * @param $emlFile
     * @param $lg
     * @param null|stdClass $cData
     * @param boolean|string $useCache
     *
     *
     * @return false|string
     * @throws core_exception_Expect
     */
    public static function extractEmailDataFromEml($emlFile, $lg = null, &$cData = null, $useCache = true)
    {
        $fRec = fileman::fetch($emlFile);

        expect($fRec);

        $source = fileman_Files::getContent($fRec->fileHnd);

        return self::extractEmailDataFromEmlFile($source, $lg, $cData, $useCache);
    }


    /**
     * Връща контактните данни от eml сорса
     *
     * @param $emlFile
     * @param $lg
     * @param null|stdClass $cData
     * @param boolean|string $useCache
     *
     *
     * @return false|string
     * @throws core_exception_Expect
     */
    public static function extractEmailDataFromEmlFile($emlSource, $lg = null, &$cData = null, $useCache = true)
    {
        expect($emlSource);

        $mime = cls::get('email_Mime');

        $mime->parseAll($emlSource);

        if (!isset($lg)) {
            $lg = $mime->getLg();
        }

        if ($mime->textPart) {
            Mode::push('text', 'plain');
            $rt = new type_Richtext();
            $textPart = $rt->toHtml($mime->textPart);
            Mode::pop('text');
        } else {
            $textPart = $mime->justTextPart;
        }

        $placeArr = array();
        $placeArr['subject'] = $mime->getSubject();
        $placeArr['from'] = $mime->getFromName();
        $placeArr['fromEmail'] = $mime->getFromEmail();
        $placeArr['email'] = $textPart;

        return self::extractEmailDataFromText($placeArr, $lg, $cData, $useCache);
    }


    /**
     * Връща контактните данни от текстовата част
     *
     * @param $placeArr $placeArr
     * @param null|string $lg
     * @param null|stdClass $cData
     * @param boolean|string $useCache
     *
     * @return false|string
     * @throws core_exception_Expect
     */
    public static function extractEmailDataFromText($placeArr, $lg = null, &$cData = null, $useCache = true)
    {
        if (!is_object($cData)) {
            $cData = new stdClass();
        }

        if (!isset($lg)) {
            $lg = core_Lg::getCurrent();
        }

        if ($lg == 'bg') {
            $text = openai_Prompt::getPromptBySystemId(openai_Prompt::$extractContactDataBg);
        } else {
            $text = openai_Prompt::getPromptBySystemId(openai_Prompt::$extractContactDataEn);
        }

        expect($text);

        $ignoreArr = self::$ignoreArr;
        $ignoreArr = arr::make($ignoreArr, true);

        $mapArr = array();

        $textArr = explode("\n", $text);
        foreach ($textArr as $key => $tStr) {
            $mArr = explode('->', $tStr);
            if ($mArr[1]) {
                $mapArr[$mArr[0]] = $mArr[1];
                $textArr[$key] = $mArr[0];
            }
        }

        $text = implode("\n", $textArr);

        $text = new ET($text);
        $text->placeArray($placeArr);

        $oRes =  openai_Api::getRes($text->getContent(), array(), $useCache);

        if ($oRes === false) {

            return false;
        }

        $oResArr = explode("\n", $oRes);
        $newResArr = array();
        foreach ($oResArr as $key => $oStr) {
            $oStr = trim($oStr);
            if (!strlen($oStr)) {

                continue;
            }

            $oStrArr = explode(":", $oStr, 2);

            $prompt = $oStrArr[0];
            $r = $oStrArr[1];

            $r = trim($r);
            if (!strlen($r)) {

                continue;
            }

            if (isset($ignoreArr[$r])) {

                continue;
            }

            if ($mp = $mapArr[$prompt]) {
                $cData->{$mp} = $r;
            }

            $newResArr[] = $prompt . ': ' . $r;
        }

        return implode("\n", $newResArr);
    }
}

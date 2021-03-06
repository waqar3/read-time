<?php
namespace Waqarahmed\ReadTime;

use Exception;

/**
 * Class ReadTime
 * @author Waqar Ahmed <ahmed@waqar.org>
 */

class ReadTime
{
    /**
     * The text content to calculate the read time.
     * @var string
     */
    public static $text;

    /**
     * Number of words read in a minute.
     * @var int
     */
    public static $wordsPerMinute = 228;

    /**
     * Total words present in the text content.
     * @var int
     */
    public static $wordCount;

    /**
     * Abbreviate minutes in output string to 'min'.
     * @var bool
     */
    public $abbreviate;

    /**
     * Number of minutes required to read text(rounded, minimum 1).
     * @var int
     */
    public static $minutes;

    /**
     * Number of minutes + seconds required to read the text.
     * Minutes are not rounded and there's no minimum value.
     * @var array<int>
     */
    public static $time;

    /**
     * English words with their translations. Default English language values.
     * @var array<string>
     * $translation['minute'] int value of number of minute
     * $translation['seconds'] int value of number of seconds
     */
    public $translation = [
        'min'     => 'min',
        'minute'  => 'minute',
        'minutes' => 'minutes',
        'read'    => 'read',
    ];

    /**
     * Whether translation language used is written right to left.
     * @var bool
     */
    public $rtl = false;

    /**
     * The language of text, reading speed of different languages vary.
     * @var string
     */
    public $language;
    
    /**
     * Constructor
     * @param string $text 
     * @param array<string>|null $translation 
     * @param bool|bool $abbreviate 
     * @param bool|bool $rtl 
     * @param string|null $language 
     * @param int $wordsPerMinute 
     * @return void
     */
    public function __construct(string $text, array $translation = null, bool $abbreviate = true, bool $rtl = false, string $language = null, int $wordsPerMinute = 228)
    {
        self::$text = $text;
        if (isset($translation)) {
            $this->doTranslations($translation);
        }
        self::$wordsPerMinute = $wordsPerMinute;
        $this->abbreviate     = $abbreviate;
        $this->rtl            = $rtl;
        if(isset($language)) {
            $this->setTextLanguage($language);
        }
        else {
            self::$wordsPerMinute = $wordsPerMinute;
        }
    }

    /**
     * Set (words per minute) reading speed of the input text language.
     *
     * The speeds are based on an article on Investigative Ophthalmology & Visual Science journal
     * https://iovs.arvojournals.org/article.aspx?articleid=2166061
     * 
     * Language Codes according to ISO 639-1
     * 
     * @return void
     */
    public function setTextLanguage(string $language): void
    {
        $speed = 228;
        switch ($language) {
            case "ar":
              $speed = 138;
              break;
            case "zh":
                $speed = 158;
              break;
            case "nl":
              $speed = 202;
              break;
            case "en":
                $speed = 228;
              break;
            case "fi":
              $speed = 161;
              break;
            case "fr":
                $speed = 195;
              break;
            case "el":
              $speed = 179;
              break;
            case "he":
                $speed = 187;
              break;
            case "it":
              $speed = 188;
              break;
            case "ja":
                $speed = 193;
              break;
            case "pl":
              $speed = 166;
              break;
            case "pt":
                $speed = 181;
              break;
            case "ru":
                $speed = 184;
              break;
            case "sl":
              $speed = 180;
              break;
            case "es":
                $speed = 218;
              break;
            case "sv":
              $speed = 199;
              break;
            case "tr":
                $speed = 166;
              break;
            
            default:
                //Default is English
                $speed = 228;
          }
          self::$wordsPerMinute = $speed;
    }

    /**
     * Count the number of words in given text content.
     *
     * @return int value of number of words in $text.
     */
    protected static function wordCount(): int
    {
        $text            = strip_tags(self::$text);
        self::$wordCount = (int) preg_match_all('/\s+/u', $text, $matches);
        return self::$wordCount;
    }
    /**
     * Calculate minutes to read the given text content.
     *
     * @param string $text The text content.
     * @return array<int> of the number of the minutes and seconds required to read the text.
     */
    public static function time(string $text): array
    {
        self::$text            = $text;
        $time                  = self::wordCount() / self::$wordsPerMinute;
        self::$time['minutes'] = (int) $time;
        self::$time['seconds'] = ($time * 60) % 60;
        return self::$time;

    }

    /**
     * Calculate time in minute, minimum 1 and rounded.
     *
     * @return void
     */

    protected static function roundMinutes(): void
    {
        self::$minutes = (int) max(round(self::wordCount() / self::$wordsPerMinute), 1);

    }
    /**
     * Get simple 'x min read' string. Abbrivated, english only.
     *
     * @param string $text The text content.
     * @return string of number of minutes and a message 'minute/s to read'.
     */

    public static function minRead(string $text)
    {
        self::$text = $text;
        self::roundMinutes();
        return self::$minutes . ' min read';
    }

    /**
     * Translate output message string.
     *
     * @param array<string> $translation
     * @return void
     */

    public function doTranslations(array $translation): void
    {
        if (!is_int(key($translation))) {
            foreach ($translation as $key => $value) {
                if (isset($this->translation[$key])) {
                    $this->translation[$key] = $value;
                }
            }
        } else {
            throw new Exception("doTranslations() only accepts an associative array");
        }
    }

    /**
     * Get multi-lingual, abberiviaed/non-abbriviated read time.
     *
     * @return string containg number of minutes and a message, 'x minute/s to read'.
     */

    public function getTime()
    {
        self::roundMinutes();

        if ($this->abbreviate) {
            //return x min read
            $result = self::$minutes . ' ' . $this->translation['min'] . ' ' . $this->translation['read'];
        } else {
            //return x minute/minutes read
            $output_text = self::$minutes > 1 ? $this->translation['minutes'] : $this->translation['minute'];
            $result      = self::$minutes . ' ' . $output_text . ' ' . $this->translation['read'];
        }

        if ($this->rtl === true) {
            $result = implode(' ', array_reverse(explode(' ', $result)));

        }

        return $result;
    }

    /**
     * Get an array of class properties and read time data.
     *
     * @return array{'minutes':int, 'time':array, 'wordCount':int, 'translation':array, 'abbreviate':bool,'wordsPerMinute':int} containg ReadTime class data.
     */

    public function getArray(): array
    {
        $this->getTime();
        return [
            'minutes'        => self::$minutes,
            'time'           => self::time(self::$text),
            'wordCount'      => self::$wordCount,
            'translation'    => $this->translation,
            'abbreviate'     => $this->abbreviate,
            'wordsPerMinute' => self::$wordsPerMinute,
        ];
    }

    /**
     * Get JSON output of class properties and read time data.
     *
     * @return string JSON object of ReadTime class data.
     */

    public function getJSON(): string
    {
        return (string) json_encode($this->getArray());

    }
}

{

}

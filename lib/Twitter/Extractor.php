<?php

class Twitter_Extractor implements Twitter_Regex {

  public function extractAll($tweet) {
    return array(
      'hashtags' => $this->extractHashtags($tweet),
      'urls'     => $this->extractURLs($tweet),
      'mentions' => $this->extractMentionedScreennames($tweet),
      'replyto'  => $this->extractReplyScreenname($tweet)
    );
  }

  public function extractHashtags($tweet) {
    preg_match_all(self::HASHTAG, $tweet, $matches);
    return $matches[3];
  }

  public function extractURLs($tweet) {
    $VALID_URL_PATTERN_STRING = '$('                 # $1 total match
      . '('.self::URL_VALID_PRECEEDING_CHARS.')'     # $2 Preceeding chracter
      . '('                                          # $3 URL
      . '(https?://|www\\.)'                         # $4 Protocol or beginning
      . '('.self::URL_VALID_DOMAIN.')'               # $5 Domain(s) (and port)
      . '(/'.self::URL_VALID_URL_PATH_CHARS.'*'      # $6 URL Path
      . self::URL_VALID_URL_PATH_ENDING_CHARS.'?)?'
      . '(\\?'.self::URL_VALID_URL_QUERY_CHARS.'*'   # $7 Query String
      . self::URL_VALID_URL_QUERY_ENDING_CHARS.')?'
      . ')'
      . ')$i';
    preg_match_all($VALID_URL_PATTERN_STRING, $tweet, $matches);
    return $matches[3];
  }

  /**
   * Extract @username references from Tweet text. A mention is an occurance of @username anywhere in a Tweet.
   *
   * @param  String text of the tweet from which to extract usernames
   * @return Array of usernames referenced (without the leading @ sign)
   */
  public function extractMentionedScreennames($tweet) {
    preg_match_all('/(^|[^a-zA-Z0-9_])[@＠]([a-zA-Z0-9_]{1,20})(?=(.|$))/', $tweet, $matches);
    $usernames = array();
    for ($i = 0; $i < sizeof($matches[2]); $i += 1) {
      if (!preg_match('/^[@＠]/', $matches[3][$i])) {
        array_push($usernames, $matches[2][$i]);
      }
    }
    return $usernames;
  }

  public function extractReplyScreenname($tweet) {
    # Single byte whitespace characters
    $whitespace  = '[';
    $whitespace .= "\x09-\x0D";     # 0x0009-0x000D White_Space # Cc   [5] <control-0009>..<control-000D>
    $whitespace .= "\x20";          # 0x0020 White_Space # Zs       SPACE
    $whitespace .= "\x85";          # 0x0085 White_Space # Cc       <control-0085>
    $whitespace .= "\xA0";          # 0x00A0 White_Space # Zs       NO-BREAK SPACE
    $whitespace .= "]|";
    # Mutli byte whitespace characters
    $whitespace .= "\xe1\x9a\x80|";                           # 0x1680 White_Space # Zs       OGHAM SPACE MARK
    $whitespace .= "\xe1\xa0\x8e|";                           # 0x180E White_Space # Zs       MONGOLIAN VOWEL SEPARATOR
    $whitespace .= "\xe2\x80[\x80-\x8a,\xa8,\xa9,\xaf\xdf]|"; # 0x2000-0x200A White_Space # Zs  [11] EN QUAD..HAIR SPACE
                                                              # 0x2028 White_Space # Zl       LINE SEPARATOR
                                                              # 0x2029 White_Space # Zp       PARAGRAPH SEPARATOR
                                                              # 0x202F White_Space # Zs       NARROW NO-BREAK SPACE
                                                              # 0x205F White_Space # Zs       MEDIUM MATHEMATICAL SPACE
    $whitespace .= "\xe3\x80\x80";                            # 0x3000 White_Space # Zs       IDEOGRAPHIC SPACE
    preg_match('/^(' . $whitespace . ')*[@＠]([a-zA-Z0-9_]{1,20})/', $tweet, $matches);
    return isset($matches[2]) ? $matches[2] : '';
  }

}
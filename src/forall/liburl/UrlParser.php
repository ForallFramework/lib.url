<?php

/**
 * @package forall.liburl
 * @author Avaq <aldwin.vlasblom@gmail.com>
 */
namespace forall\liburl;

/**
 * UrlParser class.
 */
class UrlParser
{
  
  //Regular expression to match an internet domain name.
  const MATCH_DOMAIN = '[a-zA-Z0-9\-\.]+';
  
  //Regular expression to match an IP version 4 address.
  const MATCH_IPV4 = '(?:\d+)(?:\.\d+)+';
  
  //Regular expression to match a file path.
  const MATCH_PATH = '[a-zA-Z0-9$\-.+!*\'(),&;/@]*';
  
  /**
   * Parses a complete URL into an array of segments.
   *
   * The segments returned are the following. Those with a * do not necessarily occur in
   * every URL and therefore might miss from the array:
   * 
   * * `scheme`: The scheme/protocol name used in the URL.
   * * `username`*: The user name found in the URL.
   * * `password`*: The password found in the URL.
   * * `ipv4`*: An IP (version 4) address if it was in the URL.
   * * `domain`*: An internet domain name if it was in the URL.
   * * `port`*: The port number found in the URL.
   * * `path`*: The file path found in the URL.
   * * `specific`: Non-standard / protocol-specific data found at the end of the URL.
   *
   * @param string $input The URL to parse.
   *
   * @return array The array of segments.
   */
  public static function parseUrl($input)
  {
    
    //Create the regular expression which will parse the URL.
    return self::parseAssociative($input, (
      '~^'.                                    //Begin: regular expression.
      '(?:(?<scheme>.+?):)'.                   //Scheme (required).
      '(?://'.                                 //Begin: scheme data (optional).
        '(?:'.                                 //Begin: authentication (optional).
          '(?:(?<username>.*?))'.              //User name (required).
          '(?::(?<password>.*?))?'.            //Password (optional).
        '@)?'.                                 //End: authentication.
        '(?:'.                                 //Begin: domain or IP-address.
          '(?<ipv4>'.self::MATCH_IPV4.')|'.    //IPV4 (required)
          '(?<domain>'.self::MATCH_DOMAIN.')'. //Domain (required).
        ')'.                                   //End: domain or IP-address.
        '(?::(?<port>\d+))?'.                  //Port (optional).
        '(?:(?<path>'.self::MATCH_PATH.'))?'.  //Path (optional).
      ')?'.                                    //End: scheme data.
      '(?<specific>.*)'.                       //Protocol-specific data.
      '$~'                                     //End: regular expression.
    ));
    
  }

  /**
   * Parses a partial URL into an array of segments.
   *
   * This method will do its best to identify the input as any number of segments of a URL
   * in the order in which they would occur in a full URL. It does this based on delicate
   * rules defining the small differences of every segment. For example `localhost:123`
   * would be returned as `["domain" => "localhost", "port" => "123"]`, and 
   * `localhost:123@` as `["username" => "localhost", "password" => "123"]`. However,
   * `localhost` would return as `["scheme" => "localhost"]` and has be be forced to be a
   * domain by giving `://localhost` (because the scheme occurs before `://`).
   * 
   * All possible segments are the same as those returned by {@link self::parseUrl()}.
   * Here is a list of rules defining each segment in the order of occurrence:
   * 
   * * `scheme`: A single word at the beginning of the URL.
   * * `username`: Anything followed by an @-symbol or a password.
   * * `password`: Anything between the : and @-symbols.
   * * `ipv4`/`domain`: Anything matching the rules for IP addresses or domains.
   * * `port`: A number following a colon.
   * * `path`: Anything matching the rules for a file path.
   * * `specific`: Anything else.
   *
   * @param string $input The URL partial to be parsed.
   *
   * @return [type] [description]
   */
  public static function parsePartialUrl($input)
  {
    
    //Create the regular expression which will parse the URL.
    return self::parseAssociative($input, (
      '~^(?!&)'.                             //Begin: regular expression.
      '(?:(?<scheme>[a-z]+):)?'.             //Scheme.
      '(?://)?'.                             //Scheme data opener.
      '(?:'.                                 //Begin: authentication.
        '(?:(?<username>.*?))?'.             //User name.
        '(?::(?<password>.*?))?'.            //Password.
      '@)?'.                                 //End: authentication.
      '(?:'.                                 //Begin: domain or IP-address.
        '(?<ipv4>'.self::MATCH_IPV4.')|'.    //IPV4 (required)
        '(?<domain>'.self::MATCH_DOMAIN.')'. //Domain (required).
      ')?'.                                  //End: domain or IP-address.
      '(?::(?<port>\d+))?'.                  //Port.
      '(?:(?<path>'.self::MATCH_PATH.'))?'.  //Path.
      '(?<specific>.*)'.                     //Protocol-specific data.
      '$~'                                   //End: Regular expression.
    ));
    
  }
  
  /**
   * Parse the given input using the given regular expression and return all associative matches.
   *
   * @param string $input The input to parse.
   * @param string $regex The regular expression.
   *
   * @throws ParsingException If no matches are found, or a parsing error occurred.
   *
   * @return array
   */
  private static function parseAssociative($input, $regex)
  {
    
    //Perform the regular expression and make sure it matched.
    if(!preg_match($regex, $input, $matches)){
      #TODO: Create this exception type.
      throw new ParsingException(sprintf('The given URL ("%s") could not be parsed.', $input));
    }
    
    //Iterate the matches, removing any non-associative values from them.
    foreach($matches as $key => $value){
      if(is_numeric($key)) unset($matches[$key]);
    }
    
    //Return the remaining matches.
    return $matches;
    
  }

}

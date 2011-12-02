<?php

/**
 * This helper generates the tag for embedding videos from youtube and vimeo,
 * Next features, integration with Redtube and megavideo. :D
 * 
 * @name       Video Helper
 * @author     Emerson Soares (dev.emerson@gmail.com)
 * @version    1.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php) 
 * 
 */
class VideoHelper extends HtmlHelper {

    private $apis = array(
        'youtube_image' => 'http://i.ytimg.com/vi', // Location of youtube images 
        'youtube' => 'http://www.youtube.com', // Location of youtube player 
        'vimeo' => 'http://player.vimeo.com/video'
    );

    public function embed($url, $settings = array()) {
        if ($this->getVideoSource($url) == 'youtube') {
            return $this->youTubeEmbed($url, $settings);
        } elseif ($this->getVideoSource($url) == 'vimeo') {
            return $this->vimeoEmbed($url, $settings);
        } elseif (!$this->getVideoSource($url)) {
            return $this->tag('notfound', __('Sorry, video does not exists'), array('type' => 'label', 'class' => 'error'));
        }
    }

    public function youTubeEmbed($url, $settings = array()) {

        $default_settings = array(
            'hd' => true, 
            'width' => 624,
            'height' => 369,
            'allowfullscreen' => 'true', 
            'frameborder' => 0
        );

        $settings = array_merge($default_settings, $settings);
        $video_id = $this->getVideoId($url);
        $settings['src'] = $this->apis['youtube'] . DS . 'embed' . DS . $video_id . '?hd=' . $settings['hd'];

        return $this->tag('iframe', null, array(
                    'width' => $settings['width'],
                    'height' => $settings['height'],
                    'src' => $settings['src'],
                    'frameborder' => $settings['frameborder'],
                    'allowfullscreen' => $settings['allowfullscreen'])
                ) . $this->tag('/iframe');
    }

    public function vimeoEmbed($url, $settings = array()) {
        $default_settings = array
            (
            'width' => 400,
            'height' => 225,
            'show_title' => 1,
            'show_byline' => 1,
            'show_portrait' => 0,
            'color' => '00adef',
            'allowfullscreen' => 1,
            'autoplay' => 1,
            'loop' => 1,
            'frameborder' => 0
        );
        $settings = array_merge($default_settings, $settings);

        $video_id = $this->getVideoId($url);
        $settings['src'] = $this->apis['vimeo'] . DS . $video_id . '?title=' . $settings['show_title'] . '&amp;byline=' . $settings['show_byline'] . '&amp;portrait=' . $settings['show_portrait'] . '&amp;color=' . $settings['color'] . '&amp;autoplay=' . $settings['autoplay'] . '&amp;loop=' . $settings['loop'];
        return $this->tag('iframe', null, array(
                    'src' => $settings['src'],
                    'width' => $settings['width'],
                    'height' => $settings['height'],
                    'frameborder' => $settings['frameborder'],
                    'webkitAllowFullScreen' => $settings['allowfullscreen'],
                    'mozallowfullscreen' => $settings['allowfullscreen'],
                    'allowFullScreen' => $settings['allowfullscreen']
                )) . $this->tag('/iframe');
    }

    private function getVideoId($url) {
        if ($this->getVideoSource($url) == 'youtube') {
            $params = $this->getUrlParams($url);
            return (isset($params['v']) ? $params['v'] : $url);
        } else if ($this->getVideoSource($url) == 'vimeo') {
            $path = parse_url($url, PHP_URL_PATH);
            return substr($path, 1);
        }
    }

    private function getUrlParams($url) {
        $query = parse_url($url, PHP_URL_QUERY);
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    private function getVideoSource($url) {
        $parsed_url = parse_url($url);
        $host = $parsed_url['host'];
        if (!$this->isip($host)) {
            if (!empty($host))
                $host = $this->returnDomain($host);
            else
                $host = $this->returnDomain($url);
        }
        $host = explode('.', $host);
        if (is_int(array_search('vimeo', $host)))
            return 'vimeo';
        elseif (is_int(array_search('youtube', $host)))
            return 'youtube';
        else
            return false;
    }

    private function isip($url) {
        //first of all the format of the ip address is matched 
        if (preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $url)) {
            //now all the intger values are separated 
            $parts = explode(".", $url);
            //now we need to check each part can range from 0-255 
            foreach ($parts as $ip_parts) {
                if (intval($ip_parts) > 255 || intval($ip_parts) < 0)
                    return false; //if number is not within range of 0-255 
            }
            return true;
        }
        else
            return false; //if format of ip address doesn't matches 
    }

    private function returnDomain($domainb) {
        $bits = explode('/', $domainb);
        if ($bits[0] == 'http:' || $bits[0] == 'https:') {
            $domainb = $bits[2];
        } else {
            $domainb = $bits[0];
        }
        unset($bits);
        $bits = explode('.', $domainb);
        $idz = count($bits);
        $idz-=3;
        if (strlen($bits[($idz + 2)]) == 2) {
            $url = $bits[$idz] . '.' . $bits[($idz + 1)] . '.' . $bits[($idz + 2)];
        } else if (strlen($bits[($idz + 2)]) == 0) {
            $url = $bits[($idz)] . '.' . $bits[($idz + 1)];
        } else {
            $url = $bits[($idz + 1)] . '.' . $bits[($idz + 2)];
        }
        return $url;
    }

    // Outputs Youtube video image 
    public function youTubeThumbnail($url, $size = 'thumb', $options = array()) {
        $video_id = $this->getVideoId($url);

        $accepted_sizes = array(
            'thumb' => 'default', // 120px x 90px 
            'large' => 0, // 480px x 360px 
            'thumb1' => 1, // 120px x 90px at position 25% 
            'thumb2' => 2, // 120px x 90px at position 50% 
            'thumb3' => 3  // 120px x 90px at position 75% 
        );
        $image_url = $this->apis['youtube_image'] . DS . $video_id . DS . $accepted_sizes[$size] . '.jpg';
        return $this->image($image_url, $options);
    }

}
?> 


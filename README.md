Video Helper
================================
This helper generates the tag for embedding videos from youtube and vimeo.
----------------------

```php
//usage
echo $this->Video->embed($video['Video']['url'], array(
                'width' => 450,
                'height' => 300)
            );

//advanced usage
echo $this->Video->embed($video['Video']['url'], array(
                'width' => 450,
                'height' => 300,
		'allowfullscreen'=>1,
		'loop'=>1,
		'color'=>'00adef',
		'show_title'=>1,
		'show_byline'=>1,
		'show_portrait'=>0,
		'autoplay'=>1,
		'frameborder'=>0)
            );
```
Some of these settings are applicable only to vimeo if the video is on youtube they are ignored.
------------------------

*Next features, integration with Redtube and megavideo. :D

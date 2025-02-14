<?php

class YorushikaBridge extends BridgeAbstract
{
    const NAME = 'Yorushika';
    const URI = 'https://yorushika.com';
    const DESCRIPTION = 'Return news from Yorushika\'s offical website';
    const MAINTAINER = 'Miicat_47';
    const PARAMETERS = [
        'All categories' => [
        ],
        'Only selected categories' => [
            'yorushika' => [
                'name' => 'Yorushika',
                'type' => 'checkbox',
            ],
            'suis' => [
                'name' => 'suis',
                'type' => 'checkbox',
            ],
            'n-buna' => [
                'name' => 'n-buna',
                'type' => 'checkbox',
            ],
        ]
    ];

    public function collectData()
    {
        $categories = [];
        if ($this->queriedContext == 'All categories') {
            array_push($categories, 'all');
        } else if ($this->queriedContext == 'Only selected categories') {
            if ($this->getInput('yorushika')) {
                array_push($categories, 'ヨルシカ');
            }
            if ($this->getInput('suis')) {
                array_push($categories, 'suis');
            }
            if ($this->getInput('n-buna')) {
                array_push($categories, 'n-buna');
            }
        }

        $html = getSimpleHTMLDOM('https://yorushika.com/news/5/')->find('.list--news', 0);
        $html = defaultLinkTo($html, $this->getURI());

        foreach ($html->find('.inview') as $art) {
            $item = [];

            // Get article category and check the filters
            $art_category = $art->find('.category', 0)->plaintext;
            if (!in_array('all', $categories) && !in_array($art_category, $categories)) {
                // Filtering is enabled and the category is not selected, skipping
                continue;
            }

            // Get article title
            $title = $art->find('.tit', 0)->plaintext;

            // Get article url
            $url = $art->find('a.clearfix', 0)->href;

            // Get article date
            $exp = '/\d+\.\d+\.\d+/';
            $date = $art->find('.date', 0)->plaintext;
            preg_match($exp, $date, $matches);
            $date = date_create_from_format('Y.m.d', $matches[0]);
            $date = date_format($date, 'd.m.Y');

            // Get article info
            $art_html = getSimpleHTMLDOMCached($url)->find('.text.inview', 0);
            $art_html = defaultLinkTo($art_html, $this->getURI());


            $item['uri'] = $url;
            $item['title'] = $title . ' (' . $art_category . ')';
            $item['content'] = $art_html;
            $item['timestamp'] = $date;

            $this->items[] = $item;
        }
    }
}

<?php
/***************************************
 * Qiita スタイル Markdownを実装する
 **************************************/
namespace attakei\QiitaMarkdown;

use \cebe\markdown\GithubMarkdown;


class QiitaMarkdown extends GithubMarkdown
{
    public function __construct()
    {
        // parent::__construct();
        $this->enableNewlines = true;
    }

    protected function consumeFencedCode($lines, $current)
    {
        list($block, $i) = parent::consumeFencedCode($lines, $current);
        if ( !isset($block['language']) ) {
            $block['language'] = '';
            $block['filename'] = '';
        } elseif (strpos($block['language'], ':') === false) {
            $block['filename'] = $block['language'];
            $block['language'] = '';
        } else {
            list($language, $filename) = explode(':', $block['language'], 2);
            $block['language'] = $language;
            $block['filename'] = $filename;
        }
        return [$block, $i];
    }

    /**
     * TODO: Need test case
     */
    protected function renderCode($block)
	{
        $block['filename'] = isset($block['filename']) ? $block['filename'] : '';
        $template = <<< END_OF_FORMAT
<div class="code-frame" data-lang="text">
<div class="code-lang"><span class="bold">%s</span></div>
<div class="highlight"><pre>%s
</div>
</div>
END_OF_FORMAT;
        return sprintf($template, $block['filename'], $block['content']);
	}
}

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
        $this->headlines = new \SplDoublyLinkedList();
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

    /**
     * @param int $blockLevel
     * @param \SplDoublyLinkedList $data
     * @return \SplDoublyLinkedList
     */
    protected function calcHeadline($blockLevel, $data)
    {
        if ( $data->count() == 0 ) {
            $data->push([$blockLevel]);
            return $data;
        }
        $lastHeadline = $data->top();
        $currentHeadline = $lastHeadline;
        $lastLevel = count($lastHeadline);
        if ($blockLevel == $lastLevel) {
            // 直前と同一階層なら、1要素修正
            $currentHeadline[$lastLevel-1] += 1;
            $data->push($currentHeadline);
            return $data;
        }
        if ($blockLevel > $lastLevel) {
            // 子階層の要素ならその差分だけ構築して追加
            for ($i = 0; $i < ($blockLevel - $lastLevel - 1); $i++) {
                $currentHeadline[] = 0;
            }
            $currentHeadline[] = 1;
            $data->push($currentHeadline);
            return $data;
        }
        if ($blockLevel < $lastLevel) {
            // 子階層の要素ならその差分だけ構築して追加
            for ($i = 0; $i < ($lastLevel - $blockLevel); $i++) {
                array_pop($currentHeadline);
            }
            $currentHeadline[count($currentHeadline)-1] += 1;
            $data->push($currentHeadline);
        }
        return $data;
    }

    protected function consumeHeadline($lines, $current)
    {
        list($block, $current) = parent::consumeHeadline($lines, $current);
        if (count($this->headlines) == 0) {
            $headlineInfo = [];
            for ($i = 1; $i < $block['level']; $i++) {
                $headlineInfo[] = 0;
            }
            $headlineInfo[] = 1;
            $this->headlines->push($headlineInfo);
            return [$block, $current];
        }
        $lastInfo = $this->headlines->top();
        $lastLevel = count($lastInfo);
        if ( $lastLevel == $block['level'] ) {
            $currentInfo = $lastInfo;
            $currentInfo[$lastLevel-1] += 1;
            $this->headlines->push($currentInfo);
            return [$block, $current];
        }
        if ( $lastLevel < $block['level'] ) {
            $currentInfo = $lastInfo;
            for ($sub = $block['level'] - $lastLevel; $sub > 0; $sub--) {
                array_pop($currentInfo);
            }
            $currentInfo[$block['level']-1] += 1;
            $this->headlines->push($currentInfo);
            return [$block, $current + $block['level'] - $lastLevel];
        }

    }

    /**
	 * Renders a headline
	 */
	protected function renderHeadline($block)
	{
		$tag = 'h' . $block['level'];
        $name = $this->headlines->shift();
        return sprintf(
            '<%s name="%s">%s</%s>',
            $tag, 'headline-'.implode('-', $name), $this->renderAbsy($block['content']), $tag
        );
	}
}

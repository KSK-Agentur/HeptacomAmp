<?php declare(strict_types=1);

namespace HeptacomAmp\Components\DOMAmplifier\AmplifyDOM\AmplifyStyle;

use HeptacomAmp\Components\DOMAmplifier\AmplifyDOM\IAmplifyStyle;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\Document;

class RemoveResponsiveMediaRules implements IAmplifyStyle
{
    /**
     * Process and ⚡lifies the given node and style.
     *
     * @param Document $styleDocument the style to ⚡lify
     */
    public function amplify(Document &$styleDocument)
    {
        foreach ($styleDocument->getContents() as $list) {
            if ($list instanceof AtRuleBlockList) {
                if ($list->atRuleName() == 'media') {
                    if (stripos($list->atRuleArgs(), 'min-width') !== false
                        || stripos($list->atRuleArgs(), 'print') !== false) {
                        $styleDocument->remove($list);
                    }
                }
            }
        }
    }
}

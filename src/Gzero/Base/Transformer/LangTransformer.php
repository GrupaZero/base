<?php namespace Gzero\Base\Transformer;

use Gzero\Base\Model\Language;

class LangTransformer extends AbstractTransformer {

    /**
     * Transforms lang entity
     *
     * @param Lang|array $lang Lang entity
     *
     * @return array
     */
    public function transform($lang)
    {
        $lang = $this->entityToArray(Language::class, $lang);
        return [
            'code'      => $lang['code'],
            'i18n'      => $lang['i18n'],
            'isEnabled' => (bool) $lang['is_enabled'],
            'isDefault' => (bool) $lang['is_default'],
        ];
    }
}

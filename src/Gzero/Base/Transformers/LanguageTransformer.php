<?php namespace Gzero\Base\Transformers;

use Gzero\Base\Models\Language;

/**
 * @SWG\Definition(
 *   definition="Language",
 *   type="object",
 *   required={"code", "i18n"},
 *   @SWG\Property(
 *     property="code",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="i18n",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="is_enabled",
 *     type="boolean"
 *   ),
 *   @SWG\Property(
 *     property="is_default",
 *     type="boolean"
 *   )
 * )
 */
class LanguageTransformer extends AbstractTransformer {

    /**
     * Transforms lang entity
     *
     * @param Language|array $lang Lang entity
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

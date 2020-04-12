<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors;

/**
 * Description
 *
 * @author Maximm Chichkanov
 */
interface EditorInterface
{
    const EDITOR_TYPE_WYSIWYG = 'wysiwyg';
    const EDITOR_TYPE_CODE = 'code';

    /**
     * Return edtior name.
     * @return string
     */
    public static function getEditorName();

    /**
     * Return editor type.
     * @return string
     */
    public static function getEditorType();

    /**
     * Description.
     * @return array
     */
    public static function getCallbacks($view, $config);
}

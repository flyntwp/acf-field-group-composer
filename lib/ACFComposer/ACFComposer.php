<?php

namespace ACFComposer;

class ACFComposer
{
    /**
     * Registers a local field group in Advanced Custom Fields based on a config array.
     *
     * Included fields, subfields, etc. can either contain another array defining them, or a string that stands for a WordPress filter. This filter will be applied and its result used as the field, subfield, etc.
     *
     * @param array $config Configuration array for the local field group.
     *
     * @return boolean Was the field group added or not.
     */
    public static function registerFieldGroup($config)
    {
        $fieldGroup = ResolveConfig::forFieldGroup($config);
        return acf_add_local_field_group($fieldGroup);
    }
}

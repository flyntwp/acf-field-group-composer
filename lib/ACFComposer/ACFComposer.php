<?php

namespace ACFComposer;

class ACFComposer {
  public static function registerFieldGroup($config) {
    $fieldGroup = ResolveConfig::forFieldGroup($config);
    return acf_add_local_field_group($fieldGroup);
  }
}

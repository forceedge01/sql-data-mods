<?php

namespace Genesis\SQLExtensionWrapper\Service;

class DomainModHelper
{
    /**
     * Inject defaults set by the domain mod for data mods. The supplied data takes precedence
     * and overrides any defaults in the domain mod declaration.
     *
     * @param string $domainMod
     * @param string $dataMod
     * @param array $data
     *
     * @return array
     */
    public static function injectDefaultDomainModData($domainMod, $dataMod, $data)
    {
        if (method_exists($domainMod, 'getInsertDefaults')) {
            $dataMods = $domainMod::getInsertDefaults($data);
            if (isset($dataMods[$dataMod])) {
                return array_merge($dataMods[$dataMod], $data);
            }
        }

        return $data;
    }
}

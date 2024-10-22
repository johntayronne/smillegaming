<?php

namespace ILKinguinVendor\WPDesk\PluginBuilder\Storage;

class StorageFactory
{
    /**
     * @return PluginStorage
     */
    public function create_storage()
    {
        return new \ILKinguinVendor\WPDesk\PluginBuilder\Storage\WordpressFilterStorage();
    }
}

<?php
/**
 * Enterprise-only adapter for EyouCMS plugin-presence queries.
 * Original behavior: inactive/uninstalled extensions have no metadata.
 * Commerce and member workflows are deliberately excluded by project policy.
 */
namespace app\cms\compat\model;

use app\cms\compat\Model;
use app\cms\compat\Runtime;

class ShopPublicHandle extends Model
{
    public function getWeappInfo($code='') {
        return []; // Business plugins are disabled.
    }
    public function getWeappVerifyInfo() {return $this->getWeappInfo('Verify');}
    public function getWeappPointsShop() {return $this->getWeappInfo('PointsShop');}
    public function getWeappMultiMerchant() {return $this->getWeappInfo('MultiMerchant');}
}

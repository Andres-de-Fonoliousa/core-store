<?php

namespace App\Services\Tenant;

use App\Models\DomainAlias;

class DomainVerifier
{
    public function verify(DomainAlias $alias): bool
    {
        $expected = 'store-app-verify='.$alias->verification_token;

        $records = @dns_get_record($alias->domain, DNS_TXT);

        if (! is_array($records)) {
            return false;
        }

        foreach ($records as $record) {
            $txt = $record['txt'] ?? '';

            if (in_array($expected, array_map('trim', explode(';', $txt)), true)) {
                return true;
            }
        }

        return false;
    }
}

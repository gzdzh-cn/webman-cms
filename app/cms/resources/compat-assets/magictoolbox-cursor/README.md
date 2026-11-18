# MagicToolbox zoom-out cursor compatibility asset

The original EyouCMS package references `MagicZoom/cursor/zoomout.cur` but does
not contain that file. This supplemental 32×32 Windows CUR is the actual
zoom-out cursor linked by MagicToolbox's current Magic 360 integration guide.
Its source URL, document digest, file digest, hotspot and destinations are
recorded in the parent `manifest.json`. The downloaded cursor is unmodified.

This restores a working zoom-out cursor at the original requested URL without
changing copied templates or CSS. It is a deliberate substitution from the same
vendor, not a claim to have recovered the absent 2008 binary or its exact visual
appearance. Vendor rights remain with MagicToolbox; this record does not grant
a license to the rest of the current product.

`scripts/restore_template_assets.py --write` only creates absent destination
files. An existing different cursor is preserved by refusing the operation.
No arbitrary missing URL receives this asset.

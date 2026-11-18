# MagicZoom / MagicThumb 2008 background probe

The copied upstream script creates a hidden image to measure the control bar's
CSS background. When that background is `none`, it assigns the keyword to
`img.src`, requesting a nonexistent relative URL and leaving a broken hidden
image in the document.

`scripts/build_magiczoom_compat.py` derives the two exact source variants shipped
with this project. The only correction checks for a CSS `url(...)` before loading
the existing probe and removes the unused probe for CSS keywords. Real image URL
extraction, load events, dimensions, zoom controls and copyright notices stay in
the derived scripts.

The original four copied files and templates are not modified. `AssetResolver`
serves the supplemental file at the existing asset URL only after matching the
source path, source SHA256 and supplemental SHA256. Different user scripts are
served unchanged. This is an explicit compatibility correction; the HTTP script
body differs from the old file. It does not recover missing MagicZoom cursors or
remote content images.

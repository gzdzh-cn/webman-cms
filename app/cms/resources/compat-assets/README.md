# Supplemental official-template assets

The original package references files it does not contain. These additions preserve all existing template, CSS, JavaScript and font bytes. `manifest.json` identifies the source, hashes, destinations and unresolved upstream gaps.

* `owl-carousel-2.2.1/owl.video.play.png` and `LICENSE` are the original assets from the OwlCarousel2 repository, pinned to the 2.2.1 commit in the manifest. The four deployed copies match the upstream PNG byte for byte.
* `DINCondensedC.svg` is a reproducible conversion of the bundled `DINCondensedC.ttf`, preserving its copyright, complete glyph outlines, advance widths, Unicode mappings and horizontal GPOS kerning. It is a derived supplement; the absent original SVG cannot be byte-compared. No license grant is implied. The original TTF/WOFF/EOT and stylesheet remain unchanged.

No build dependency is needed to serve the eight deployed files. The deployed font and patch bytes are preserved upstream artifacts. Verify, build and restore them through the PHP `cms:assets` commands and their SHA-locked source archive. The historical font-generation scripts are not part of the supported maintenance workflow.

The original missing cursor and external image failures remain separate acceptance gaps; no empty or unrelated replacement is installed.

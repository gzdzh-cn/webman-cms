-- Runtime business persistence, not installation status.
CREATE TABLE `cms_cms_state_records` (
 `scope` varchar(96) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 `name` varchar(191) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 `payload` longblob NOT NULL,
 `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 `updated_at` bigint unsigned NOT NULL,
 PRIMARY KEY (`scope`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `cms_cms_state_objects` (
 `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 `payload` longblob NOT NULL,
 `bytes` bigint unsigned NOT NULL,
 `created_at` bigint unsigned NOT NULL,
 PRIMARY KEY (`sha256`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `cms_cms_state_object_refs` (
 `scope` varchar(96) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 `name` varchar(191) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 PRIMARY KEY (`scope`,`name`), KEY `object_sha256` (`sha256`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

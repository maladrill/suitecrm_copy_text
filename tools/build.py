#!/usr/bin/env python3
"""Build a reproducible SuiteCRM Module Loader archive from tracked sources."""
import argparse
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

ROOT = Path(__file__).resolve().parents[1]
SOURCES = (
    "manifest.php",
    "README.txt",
    "LICENSE.txt",
    "scripts/pre_install.php",
    "scripts/post_install.php",
    "scripts/post_uninstall.php",
    "src/custom/include/DetailView/DetailView.tpl",
    "src/custom/include/SQS_CopyText/copy.css",
    "src/custom/include/SQS_CopyText/copy.js",
    "src/custom/include/Smarty/plugins/function.sqs_copy_option.php",
    "src/custom/modules/ModuleBuilder/controller.php",
    "src/custom/modules/ModuleBuilder/tpls/MBModule/field.tpl",
    "src/custom/Extension/modules/ModuleBuilder/Ext/Language/en_us.sqs_copy_text.php",
    "src/custom/Extension/modules/ModuleBuilder/Ext/Language/pl_PL.sqs_copy_text.php",
)


def build(destination):
    missing = [name for name in SOURCES if not (ROOT / name).is_file()]
    if missing:
        raise FileNotFoundError("Missing package sources: " + ", ".join(missing))
    destination.parent.mkdir(parents=True, exist_ok=True)
    with ZipFile(destination, "w", compression=ZIP_DEFLATED, compresslevel=9) as archive:
        for name in sorted(SOURCES):
            info = ZipInfo(name, date_time=(2026, 11, 24, 0, 0, 0))
            info.compress_type = ZIP_DEFLATED
            info.external_attr = 0o100644 << 16
            archive.writestr(info, (ROOT / name).read_bytes(), compress_type=ZIP_DEFLATED, compresslevel=9)
        if archive.testzip() is not None:
            raise RuntimeError("Invalid package ZIP")
    print(destination)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--output", type=Path, default=ROOT / "dist/SQS_Copy_Text-1.0.1.zip")
    args = parser.parse_args()
    build(args.output.resolve())

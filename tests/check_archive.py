"""Verify the installer does not replace the active Detail View template."""
from pathlib import Path
from zipfile import ZipFile

archive = Path("dist/SQS_Copy_Text-1.0.7.zip")
with ZipFile(archive) as package:
    paths = set(package.namelist())
    assert "src/custom/include/DetailView/DetailView.tpl" not in paths
    assert "src/custom/Extension/application/Ext/LogicHooks/sqs_copy_text.php" in paths
    assert "src/custom/Extension/application/Ext/Language/pl_PL.sqs_copy_text.php" in paths
    assert "src/custom/Extension/application/Ext/Language/en_us.sqs_copy_text.php" in paths
    manifest = package.read("manifest.php").decode("utf-8")
    assert "'to' => 'custom/include/DetailView/DetailView.tpl'" not in manifest

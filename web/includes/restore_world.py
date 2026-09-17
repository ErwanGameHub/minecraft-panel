import gzip
import os
from pathlib import Path, PurePosixPath
import shutil
import sys
import tarfile
import tempfile
import uuid
import zipfile


def extract_world(archive, destination):
    if archive.name.lower().endswith('.tar.gz'):
        # TAR readers can stop before the gzip trailer; validate the entire stream.
        with gzip.open(archive, 'rb') as stream:
            while stream.read(1024 * 1024):
                pass
        container = tarfile.open(archive, 'r:gz')
    else:
        container = zipfile.ZipFile(archive)
    with container:
        is_zip = isinstance(container, zipfile.ZipFile)
        members = container.infolist() if is_zip else container.getmembers()
        seen = set()
        for member in members:
            name = member.filename if is_zip else member.name
            path = PurePosixPath(name)
            if path.is_absolute() or '..' in path.parts or '\\' in name or ':' in name:
                raise ValueError('Unsafe archive path')
            if not path.parts:
                continue
            if path in seen:
                raise ValueError('Duplicate archive entry')
            seen.add(path)
            directory = member.is_dir() if is_zip else member.isdir()
            if is_zip:
                kind = (member.external_attr >> 16) & 0o170000
                if kind not in (0, 0o040000, 0o100000):
                    raise ValueError('Archive links and special files are not supported')
            elif not (member.isfile() or directory):
                raise ValueError('Archive links and special files are not supported')
            target = destination.joinpath(*path.parts)
            if directory:
                target.mkdir(parents=True, exist_ok=True)
            else:
                target.parent.mkdir(parents=True, exist_ok=True)
                source = container.open(member) if is_zip else container.extractfile(member)
                with source, target.open('xb') as output:
                    shutil.copyfileobj(source, output)
    roots = list(destination.iterdir())
    if len(roots) != 1 or not roots[0].is_dir():
        raise ValueError('Backup must contain exactly one world folder')
    world = roots[0]
    if not (world / 'level.dat').is_file() or not (world / 'db').is_dir():
        raise ValueError('World must contain level.dat and db/')
    return world


def restore(archive, root):
    worlds = root / 'Server/worlds'
    worlds.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='.restore-', dir=worlds) as temporary:
        world = extract_world(archive, Path(temporary))
        active = 'Bedrock level'
        for line in (root / 'Server/server.properties').read_text().splitlines():
            if line.startswith('level-name='):
                active = line.partition('=')[2]
        if world.name != active:
            raise ValueError('World folder does not match level-name; update Startup settings first')
        target = worlds / world.name
        preserved = None
        if target.exists():
            backup_dir = root / 'BackupWorlds'
            backup_dir.mkdir(exist_ok=True)
            preserved = backup_dir / ('before-restore-' + uuid.uuid4().hex)
            os.rename(target, preserved)
        try:
            os.rename(world, target)
        except Exception:
            if preserved is not None:
                os.rename(preserved, target)
            raise
        print('Restore finished! World replaced successfully.')
        if preserved:
            print('Previous world preserved at ' + str(preserved))
        print('Uploaded archive retained for recovery.')


if __name__ == '__main__':
    try:
        restore(Path(sys.argv[1]), Path('/home/minecraft'))
    except Exception as error:
        print('Restore failed: ' + str(error), file=sys.stderr)
        sys.exit(1)

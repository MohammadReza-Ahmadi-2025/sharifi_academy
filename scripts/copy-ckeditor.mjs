import { execSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const CKEDITOR_VERSION = '4.22.1';
const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const dest = join(root, 'public/ckeditor');
const versionMarker = join(dest, '.installed-version');
const nodeModulesSrc = join(root, 'node_modules/ckeditor4');

if (
    existsSync(versionMarker)
    && readFileSync(versionMarker, 'utf8').trim() === CKEDITOR_VERSION
    && existsSync(join(dest, 'ckeditor.js'))
) {
    console.log(`[copy-ckeditor] skip (already ${CKEDITOR_VERSION})`);
    process.exit(0);
}

function packageVersion(dir) {
    try {
        const pkg = JSON.parse(readFileSync(join(dir, 'package.json'), 'utf8'));

        return pkg.version ?? null;
    } catch {
        return null;
    }
}

function installToPublic(src) {
    if (existsSync(dest)) {
        rmSync(dest, { recursive: true, force: true });
    }
    mkdirSync(dirname(dest), { recursive: true });
    cpSync(src, dest, { recursive: true });
    patchEditorConfig();
    writeFileSync(versionMarker, CKEDITOR_VERSION);
    console.log(`[copy-ckeditor] installed ${CKEDITOR_VERSION} to public/ckeditor`);
}

function patchEditorConfig() {
    const configPath = join(dest, 'config.js');
    if (!existsSync(configPath)) {
        return;
    }

    let content = readFileSync(configPath, 'utf8');
    if (content.includes('versionCheck')) {
        return;
    }

    content = content.replace(
        'CKEDITOR.editorConfig = function( config ) {',
        'CKEDITOR.editorConfig = function( config ) {\n\tconfig.versionCheck = false;',
    );
    writeFileSync(configPath, content);
}

function downloadRelease() {
    const cacheDir = join(root, 'storage/framework/cache');
    const tmpDir = join(cacheDir, `ckeditor4-releases-${CKEDITOR_VERSION}`);
    const extracted = join(tmpDir, `ckeditor4-releases-${CKEDITOR_VERSION}`);

    mkdirSync(cacheDir, { recursive: true });
    rmSync(tmpDir, { recursive: true, force: true });
    mkdirSync(tmpDir, { recursive: true });

    const url = `https://github.com/ckeditor/ckeditor4-releases/archive/refs/tags/${CKEDITOR_VERSION}.tar.gz`;
    execSync(`curl -fsSL "${url}" | tar -xz -C "${tmpDir}"`, { stdio: 'inherit' });

    if (!existsSync(extracted)) {
        throw new Error(`Expected extracted folder: ${extracted}`);
    }

    installToPublic(extracted);
}

const npmVersion = existsSync(nodeModulesSrc) ? packageVersion(nodeModulesSrc) : null;

if (npmVersion === CKEDITOR_VERSION) {
    installToPublic(nodeModulesSrc);
} else {
    if (npmVersion) {
        console.warn(`[copy-ckeditor] node_modules has ckeditor4@${npmVersion}; fetching ${CKEDITOR_VERSION}.`);
    }
    downloadRelease();
}

#!/usr/bin/env node

const { build } = require('esbuild');
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const args = process.argv.slice(2);
const isDev = args.includes('--dev');
const isWatch = args.includes('--watch');
const isBuild = args.includes('--build') || (!isDev && !isWatch);

const srcDir = path.join(__dirname, '../resources');
const distDir = path.join(__dirname, '../resources/dist');

// Ensure dist directory exists
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}

async function buildAssets() {
    try {
        // Build JavaScript
        if (isWatch) {
            // Use context for watch mode
            const context = await build({
                entryPoints: [path.join(srcDir, 'js/shell-terminal.js')],
                bundle: true,
                outfile: path.join(distDir, 'shell-terminal.js'),
                format: 'iife',
                globalName: 'FilaShellTerminal',
                minify: false,
                sourcemap: true,
                target: ['es2020'],
            });
            
            await context.watch();
        } else {
            // Regular build
            await build({
                entryPoints: [path.join(srcDir, 'js/shell-terminal.js')],
                bundle: true,
                outfile: path.join(distDir, 'shell-terminal.js'),
                format: 'iife',
                globalName: 'FilaShellTerminal',
                minify: !isDev,
                sourcemap: isDev,
                target: ['es2020'],
            });
        }

        // Build CSS with PostCSS
        const cssInput = path.join(srcDir, 'css/shell-terminal.css');
        const cssOutput = path.join(distDir, 'shell-terminal.css');
        
        if (fs.existsSync(cssInput)) {
            const postcssArgs = [
                cssInput,
                '--output', cssOutput,
                '--use', 'postcss-nesting',
                '--use', 'cssnano',
            ];
            
            if (isDev) {
                postcssArgs.push('--map');
            }
            
            execSync(`npx postcss ${postcssArgs.join(' ')}`, { stdio: 'inherit' });
        }

        // Copy Xterm.js assets
        const xtermAssets = [
            { src: 'node_modules/xterm/css/xterm.css', dest: 'css/xterm.css' },
            { src: 'node_modules/xterm/lib/xterm.js', dest: 'js/xterm.js' },
            { src: 'node_modules/xterm-addon-fit/lib/xterm-addon-fit.js', dest: 'js/xterm-addon-fit.js' },
            { src: 'node_modules/xterm-addon-web-links/lib/xterm-addon-web-links.js', dest: 'js/xterm-addon-web-links.js' },
        ];

        for (const asset of xtermAssets) {
            const srcPath = path.join(__dirname, '..', asset.src);
            const destPath = path.join(distDir, asset.dest);
            const destDir = path.dirname(destPath);
            
            if (fs.existsSync(srcPath)) {
                if (!fs.existsSync(destDir)) {
                    fs.mkdirSync(destDir, { recursive: true });
                }
                fs.copyFileSync(srcPath, destPath);
                console.log(`✓ Copied ${asset.src} to ${asset.dest}`);
            }
        }

        console.log('🎉 Assets built successfully!');
        
        if (isWatch) {
            console.log('👀 Watching for changes...');
        }
    } catch (error) {
        console.error('❌ Build failed:', error);
        process.exit(1);
    }
}

// Main execution
if (isWatch) {
    console.log('🚀 Starting watch mode...');
    buildAssets();
} else if (isBuild) {
    console.log('🔨 Building for production...');
    buildAssets().then(() => process.exit(0));
} else {
    console.log('🚀 Starting development build...');
    buildAssets().then(() => process.exit(0));
}

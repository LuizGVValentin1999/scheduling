// Build separado para o widget embutível.
// Gera um único arquivo JS autocontido que pode ser incluído
// em qualquer site via <script src="...scheduling-widget.js"></script>
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react()],
    build: {
        outDir: 'public/widget',
        lib: {
            entry: 'resources/js/widget/BookingWidget.jsx',
            name: 'SchedulingWidget',
            fileName: 'scheduling-widget',
            formats: ['iife'], // bundle único, sem módulo
        },
        rollupOptions: {
            // React inline para o widget ser autocontido
            external: [],
        },
    },
});

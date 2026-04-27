import { createTheme } from '@mui/material/styles';
import { ptBR } from '@mui/material/locale';

const theme = createTheme(
    {
        palette: {
            primary: {
                main: '#7c3aed', // roxo — coerente com a sidebar
            },
            secondary: {
                main: '#0ea5e9',
            },
            background: {
                default: '#f8fafc',
            },
        },
        shape: {
            borderRadius: 10,
        },
        typography: {
            fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
        },
        components: {
            MuiButton: {
                defaultProps: { disableElevation: true },
                styleOverrides: {
                    root: { textTransform: 'none', fontWeight: 600 },
                },
            },
            MuiCard: {
                styleOverrides: {
                    root: { boxShadow: '0 1px 4px rgba(0,0,0,.06)', borderRadius: 12 },
                },
            },
        },
    },
    ptBR // localização pt-BR para MUI (labels de datepicker, etc.)
);

export default theme;

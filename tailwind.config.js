/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        secondary: {
          light: '#6C9C9C',
          DEFAULT: '#4C7A8D',
          dark: '#2C3747',
        },
        main: {
          light: '#3a5e6ee6',
          DEFAULT: '#4C7B8F',
          dark: '#355461',
        },
        button: {
          create: '#00ABEA',
          save: '#00B400',
          delete: '#FF1212',
        }
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [],
}


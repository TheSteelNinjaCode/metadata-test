/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/app/**/*.{php,html,js,css}"
],
  theme: {
    extend: {},
  },
  plugins: [require("daisyui")],
}


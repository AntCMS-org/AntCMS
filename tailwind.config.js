/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/Themes/Tailwind/*.{html,js}", "./src/AntCMS/*.php"], // Including the PHP files because some of the components are generated from PHP files
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}

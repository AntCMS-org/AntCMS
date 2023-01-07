/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/Themes/Default/Templates/*.{html,js}"],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}

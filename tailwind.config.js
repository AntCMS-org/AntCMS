/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/Themes/Tailwind/Templates/*.{html,js}"],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}
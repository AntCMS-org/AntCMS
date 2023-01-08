/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/Themes/Default/Templates/*.{html,js}"],
  theme: {
    extend: {
      typography ({ theme }) {
        return {
          DEFAULT: {
            css: {
              'code::before': {
                content: 'none',
              },
              'code::after': {
                content: 'none'
              },
              code: {
                backgroundColor: theme('colors.zinc.700'),
                color: theme('colors.zinc.100'),
                paddingLeft: theme('spacing[1.5]'),
                paddingRight: theme('spacing[1.5]'),
                paddingTop: theme('spacing.1'),
                paddingBottom: theme('spacing.1'),
                borderRadius: theme('borderRadius.DEFAULT'),
              },
            }
          },
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/forms'),
  ],
}
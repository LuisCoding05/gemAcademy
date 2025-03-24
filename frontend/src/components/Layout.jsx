import { Outlet } from 'react-router-dom'
import { Navbar } from './Navbar'
import { Header } from './Header'
import { Footer } from './Footer'
import { Copy } from './Copy'
import { useTheme } from './ThemeContext'

export const Layout = () => {
  const { isDarkMode } = useTheme();

  return (
    <div className={isDarkMode ? 'dark-mode' : ''}>
      <Navbar />
      <Header />
      <main className="container mt-5 pt-5">
        <Outlet />
      </main>
      <Footer />
      <Copy />
    </div>
  )
}
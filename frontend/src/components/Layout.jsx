import React from 'react';
import { Outlet } from 'react-router-dom'
import { Navbar } from './Navbar'
import { Header } from './Header'
import { Footer } from './Footer'
import { Copy } from './Copy'
import { useTheme } from './ThemeContext'

export const Layout = () => {
  const { isDarkMode } = useTheme();

  return (
    <div className="d-flex flex-column min-vh-100">
      <Navbar />
      <Header />
      <main className="flex-grow-1 container py-5" style={{ marginTop: '4rem', marginBottom: '2rem' }}>
        <Outlet />
      </main>
      <Footer />
      <Copy />
    </div>
  )
}
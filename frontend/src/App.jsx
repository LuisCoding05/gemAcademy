import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Layout } from './components/Layout'
import { Home } from './components/Home'
import { MainContent } from './components/MainContent'
import { Aside } from './components/Aside'
import { Navbar } from './components/Navbar'
import { Copy } from './components/Copy'
import { ThemeProvider } from './components/ThemeContext';

function App() {
  return (
    <ThemeProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<Layout />}>
            <Route index element={
              <div className="container">
                <div className="row">
                  <MainContent />
                  <Aside />
                </div>
              </div>
            } />
          </Route>
          
          <Route path="/home" element={
            <div className="wrapper">
              <Navbar />
              <Home />
              <Copy />
            </div>
          } />
        </Routes>
      </BrowserRouter>
    </ThemeProvider>
  )
}

export default App
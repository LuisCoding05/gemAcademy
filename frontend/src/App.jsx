import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Layout } from './components/Layout'
import { Home } from './components/Home'
import { MainContent } from './components/MainContent'
import { Aside } from './components/Aside'
import { Navbar } from './components/Navbar'
import { Copy } from './components/Copy'
import { ThemeProvider } from './components/ThemeContext';
import MainRegister from './components/register/MainRegister'
import MainLogin from './components/register/MainLogin'
import VerificationResetPassword from './components/register/VerificationResetPassword'

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
          <Route path="/register" element={
            <div className="wrapper">
              <MainRegister />
              <Copy />
            </div>
          } />
          <Route path="/login" element={
            <div className="wrapper">
              <MainLogin />
              <Copy />
            </div>
          } />

        <Route path="/verify" element={
            <div className="wrapper">
              <VerificationResetPassword />
              <Copy />
            </div>
          } />
        </Routes>
      </BrowserRouter>
    </ThemeProvider>
  )
}

export default App
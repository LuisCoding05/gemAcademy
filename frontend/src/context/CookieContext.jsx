import { createContext, useState, useContext, useEffect } from 'react';

const CookieContext = createContext();

export const CookieProvider = ({ children }) => {
  const [cookieConsent, setCookieConsent] = useState(() => {
    const saved = localStorage.getItem('cookieConsent');
    return saved ? JSON.parse(saved) : null;
  });

  useEffect(() => {
    if (cookieConsent !== null) {
      localStorage.setItem('cookieConsent', JSON.stringify(cookieConsent));
    }
  }, [cookieConsent]);

  const acceptCookies = (essentialOnly = false) => {
    setCookieConsent({
      essential: true,
      youtube: !essentialOnly
    });
  };

  const value = {
    cookieConsent,
    acceptCookies
  };

  return (
    <CookieContext.Provider value={value}>
      {children}
    </CookieContext.Provider>
  );
};

export const useCookies = () => {
  const context = useContext(CookieContext);
  if (!context) {
    throw new Error('useCookies debe ser usado dentro de un CookieProvider');
  }
  return context;
};
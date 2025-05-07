import React, { useState, useEffect } from 'react';
import axios from '../../utils/axios';
import { Link } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import { useAuth } from '../../context/AuthContext';
import Loader from '../common/Loader';
import './../../styles/ranking/GlobalRanking.css';

const GlobalRanking = () => {
  const [rankingData, setRankingData] = useState(null);
  const [loading, setLoading] = useState(true);
  const { isDarkMode } = useTheme();
  const { isAuthenticated } = useAuth();

  useEffect(() => {
    const fetchRanking = async () => {
      try {
        const response = await axios.get('/api/ranking');
        setRankingData(response.data);
        setLoading(false);
      } catch (error) {
        console.error('Error fetching ranking:', error);
        setLoading(false);
      }
    };

    fetchRanking();
  }, []);

  if (loading) {
    return <Loader size="large" />;
  }

  const getMedalEmoji = (position) => {
    switch (position) {
      case 1:
        return '🥇';
      case 2:
        return '🥈';
      case 3:
        return '🥉';
      default:
        return null;
    }
  };

  return (
    <div className={`ranking-container ${isDarkMode ? 'dark' : ''}`}>
      <h1 className="ranking-title neon-text">Ranking Global</h1>
      
      {isAuthenticated && rankingData?.userPosition && (
        <div className="user-position-container">
          <div className="neon-box">
            <span className="position-text">Tu posición actual</span>
            <span className="position-number">{rankingData.userPosition}</span>
          </div>
        </div>
      )}

      <div className="ranking-list">
        {rankingData?.top10.map((user, index) => (
          <div 
            key={user.id} 
            className={`ranking-item ${index < 3 ? 'top-three' : ''}`}
          >
            <div className="position-indicator">
              {getMedalEmoji(index + 1) || (index + 1)}
            </div>
            
            <Link to={`/profile/${user.id}`} className="user-info">
              <div className="avatar-container">
                <img 
                  src={user.imagen || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'} 
                  alt={user.username}
                  className="user-avatar"
                />
              </div>
              
              <div className="user-details">
                <span className="username neon-text-subtle">{user.username}</span>
                <span className="full-name">{user.nombre}</span>
                <div className="level-info">
                  <span className="level-badge">
                    Nivel {user.nivel.numero}
                  </span>
                  <span className="level-name">{user.nivel.nombre}</span>
                </div>
              </div>
            </Link>

            <div className="points-container">
              <span className="points neon-text">{user.puntos}</span>
              <span className="points-label">puntos</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default GlobalRanking;
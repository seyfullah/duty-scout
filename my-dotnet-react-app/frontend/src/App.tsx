import React, { useState } from 'react';
import Groups from './components/Groups';
import Members from './components/Members';
import GroupMembers from './components/GroupMembers';
import './styles/app.css';

const App: React.FC = () => {
  const [currentPage, setCurrentPage] = useState<'groups' | 'members' | 'groupMembers'>('groups');

  return (
    <div className="app-container">
      <nav className="navbar">
        <div className="navbar-content">
          <h1 className="navbar-title">Takip Gözcüsü</h1>
          <div className="navbar-buttons">
            <button
              className={`navbar-button ${currentPage === 'groups' ? 'active' : ''}`}
              onClick={() => setCurrentPage('groups')}
            >
              Gruplar
            </button>
            <button
              className={`navbar-button ${currentPage === 'groupMembers' ? 'active' : ''}`}
              onClick={() => setCurrentPage('groupMembers')}
            >
              Grup Üyeleri
            </button>
            <button
              className={`navbar-button ${currentPage === 'members' ? 'active' : ''}`}
              onClick={() => setCurrentPage('members')}
            >
              Tüm Üyeler
            </button>
          </div>
        </div>
      </nav>

      <div className="content">
        {currentPage === 'groups' && <Groups />}
        {currentPage === 'groupMembers' && <GroupMembers />}
        {currentPage === 'members' && <Members />}
      </div>
    </div>
  );
};

export default App;
import React, { useEffect, useState } from 'react';
import '../styles/common.css';
import '../styles/groupMembers.css';

interface Member {
  id: string;
  name: string;
}

interface Group {
  id: string;
  name: string;
  responsible: string;
  members: Member[];
  subgroups?: Group[];
}

const API = 'http://localhost:5000/api/groups';

const GroupMembers: React.FC = () => {
  const [groups, setGroups] = useState<Group[]>([]);
  const [selectedGroupId, setSelectedGroupId] = useState<string | null>(null);
  const [selectedGroup, setSelectedGroup] = useState<Group | null>(null);
  const [newMemberName, setNewMemberName] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(API);
      if (!res.ok) throw new Error(`Server returned ${res.status}`);
      const data = await res.json();
      setGroups(data);
      
      if (selectedGroupId) {
        const findGroup = (grps: Group[]): Group | null => {
          for (const g of grps) {
            if (g.id === selectedGroupId) return g;
            if (g.subgroups) {
              const found = findGroup(g.subgroups);
              if (found) return found;
            }
          }
          return null;
        };
        setSelectedGroup(findGroup(data));
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, []);

  const handleGroupSelect = (groupId: string) => {
    setSelectedGroupId(groupId);
    const findGroup = (grps: Group[]): Group | null => {
      for (const g of grps) {
        if (g.id === groupId) return g;
        if (g.subgroups) {
          const found = findGroup(g.subgroups);
          if (found) return found;
        }
      }
      return null;
    };
    setSelectedGroup(findGroup(groups));
    setNewMemberName('');
  };

  const addMember = async () => {
    if (!newMemberName.trim() || !selectedGroupId) return;
    try {
      await fetch(`${API}/${selectedGroupId}/members`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: newMemberName.trim() }),
      });
      setNewMemberName('');
      await load();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    }
  };

  const deleteMember = async (memberId: string) => {
    if (!selectedGroupId) return;
    if (window.confirm('Üyeyi silmek istediğinize emin misiniz?')) {
      try {
        await fetch(`${API}/${selectedGroupId}/members/${memberId}`, { method: 'DELETE' });
        await load();
      } catch (err: unknown) {
        setError(err instanceof Error ? err.message : String(err));
      }
    }
  };

  const getAllGroups = (grps: Group[]): Group[] => {
    let result: Group[] = [];
    grps.forEach(g => {
      result.push(g);
      if (g.subgroups) {
        result = [...result, ...getAllGroups(g.subgroups)];
      }
    });
    return result;
  };

  const allGroups = getAllGroups(groups);

  return (
    <div className="group-members-container">
      <h1 className="title">Grup Üyelerini Yönet</h1>

      {error && <div className="error">{error}</div>}
      {loading && <p>Yükleniyor...</p>}

      {!loading && (
        <>
          <div className="paper">
            <h3>Grup Seçin</h3>
            <select
              className="input group-select"
              value={selectedGroupId || ''}
              onChange={e => handleGroupSelect(e.target.value)}
            >
              <option value="">-- Grup Seçiniz --</option>
              {allGroups.map(g => (
                <option key={g.id} value={g.id}>
                  {g.name}
                </option>
              ))}
            </select>
          </div>

          {selectedGroup && (
            <>
              <div className="paper">
                <div className="group-info">
                  <h2 className="group-info-title">{selectedGroup.name}</h2>
                  <p className="group-info-subtitle">
                    Sorumlu: {selectedGroup.responsible || '-'}
                  </p>
                </div>

                <div className="divider" />

                <h3>Yeni Üye Ekle</h3>
                <div className="row">
                  <input
                    type="text"
                    className="input input-flex"
                    placeholder="Üye adı"
                    value={newMemberName}
                    onChange={e => setNewMemberName(e.target.value)}
                    onKeyPress={e => e.key === 'Enter' && addMember()}
                  />
                  <button
                    className="button button-primary"
                    onClick={addMember}
                  >
                    Üye Ekle
                  </button>
                </div>
              </div>

              <div className="paper">
                <h3>Üyeler ({selectedGroup.members?.length || 0})</h3>
                {selectedGroup.members && selectedGroup.members.length > 0 ? (
                  <div className="members-list">
                    {selectedGroup.members.map(member => (
                      <div key={member.id} className="member-item">
                        <div className="member-info">
                          <span className="member-name">{member.name}</span>
                        </div>
                        <button
                          className="button button-danger button-small"
                          onClick={() => deleteMember(member.id)}
                        >
                          Sil
                        </button>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="no-members">Bu gruba ait üye bulunmamaktadır.</p>
                )}
              </div>
            </>
          )}

          {!selectedGroup && (
            <div className="paper">
              <p className="no-selection">Lütfen bir grup seçiniz.</p>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default GroupMembers;
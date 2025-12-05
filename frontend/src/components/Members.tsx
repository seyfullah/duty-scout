import React, { useEffect, useState } from 'react';
import '../styles/common.css';
import '../styles/members.css';

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

const Members: React.FC = () => {
  const [groups, setGroups] = useState<Group[]>([]);
  const [allMembers, setAllMembers] = useState<{ groupId: string; groupName: string; member: Member }[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedGroupId, setSelectedGroupId] = useState<string | null>(null);
  const [hoveredRowIdx, setHoveredRowIdx] = useState<number | null>(null);

  const load = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(API);
      if (!res.ok) throw new Error(`Server returned ${res.status}`);
      const data = await res.json();
      setGroups(data);
      
      const members: { groupId: string; groupName: string; member: Member }[] = [];
      const flattenMembers = (grps: Group[]) => {
        grps.forEach(g => {
          g.members?.forEach(m => {
            members.push({ groupId: g.id, groupName: g.name, member: m });
          });
          if (g.subgroups) flattenMembers(g.subgroups);
        });
      };
      flattenMembers(data);
      setAllMembers(members);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, []);

  const deleteMember = async (groupId: string, memberId: string) => {
    if (window.confirm('Üyeyi silmek istediğinize emin misiniz?')) {
      try {
        await fetch(`${API}/${groupId}/members/${memberId}`, { method: 'DELETE' });
        await load();
      } catch (err: unknown) {
        setError(err instanceof Error ? err.message : String(err));
      }
    }
  };

  const filteredMembers = allMembers.filter(item => {
    const matchesSearch = item.member.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                          item.groupName.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesGroup = !selectedGroupId || item.groupId === selectedGroupId;
    return matchesSearch && matchesGroup;
  });

  return (
    <div className="members-container">
      <h1 className="title">Üye Yönetimi</h1>

      {error && <div className="error">{error}</div>}
      {loading && <p>Yükleniyor...</p>}

      {!loading && (
        <>
          <div className="stats">
            <div className="stat-card">
              <div className="stat-number">{allMembers.length}</div>
              <div className="stat-label">Toplam Üye</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">{groups.length}</div>
              <div className="stat-label">Grup Sayısı</div>
            </div>
            <div className="stat-card">
              <div className="stat-number">{filteredMembers.length}</div>
              <div className="stat-label">Filtrelenen Üye</div>
            </div>
          </div>

          <div className="paper">
            <h3>Filtreleme</h3>
            <div className="filter-row">
              <div className="filter-input">
                <input
                  type="text"
                  className="input"
                  placeholder="Üye adı veya grup adına göre ara..."
                  value={searchTerm}
                  onChange={e => setSearchTerm(e.target.value)}
                />
              </div>
              <select
                className="filter-select"
                value={selectedGroupId || ''}
                onChange={e => setSelectedGroupId(e.target.value || null)}
              >
                <option value="">Tüm Gruplar</option>
                {groups.map(g => (
                  <option key={g.id} value={g.id}>
                    {g.name}
                  </option>
                ))}
              </select>
              <button
                className="button button-primary"
                onClick={() => { setSearchTerm(''); setSelectedGroupId(null); }}
              >
                Temizle
              </button>
            </div>
          </div>

          {filteredMembers.length > 0 ? (
            <div className="paper">
              <table className="table">
                <thead>
                  <tr>
                    <th>Üye Adı</th>
                    <th>Grup Adı</th>
                    <th>İşlem</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredMembers.map((item, idx) => (
                    <tr
                      key={idx}
                      onMouseEnter={() => setHoveredRowIdx(idx)}
                      onMouseLeave={() => setHoveredRowIdx(null)}
                    >
                      <td>{item.member.name}</td>
                      <td>{item.groupName}</td>
                      <td>
                        <button
                          className="button button-danger"
                          onClick={() => deleteMember(item.groupId, item.member.id)}
                        >
                          Sil
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="paper">
              <p className="no-results">Sonuç bulunamadı.</p>
            </div>
          )}
        </>
      )}
    </div>
  );
};

export default Members;
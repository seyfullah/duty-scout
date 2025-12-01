import React, { useEffect, useState } from 'react';
import '../styles/common.css';
import '../styles/groups.css';

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

const Groups: React.FC = () => {
  const [groups, setGroups] = useState<Group[]>([]);
  const [newGroupName, setNewGroupName] = useState('');
  const [newGroupResponsible, setNewGroupResponsible] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [editingGroup, setEditingGroup] = useState<{ id: string; name: string; responsible: string } | null>(null);

  const load = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(API);
      if (!res.ok) throw new Error(`Server returned ${res.status}`);
      const data = await res.json();
      setGroups(data);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load();
  }, []);

  const createGroup = async () => {
    if (!newGroupName.trim()) return;
    try {
      await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newGroupName.trim(),
          responsible: newGroupResponsible.trim(),
        }),
      });
      setNewGroupName('');
      setNewGroupResponsible('');
      await load();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    }
  };

  const updateGroup = async (id: string, name: string, responsible: string) => {
    if (!name.trim()) return;
    try {
      await fetch(`${API}/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name.trim(), responsible: responsible.trim() }),
      });
      setEditingGroup(null);
      await load();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    }
  };

  const deleteGroup = async (id: string) => {
    if (window.confirm('Grubu ve tüm üyelerini silmek istediğinize emin misiniz?')) {
      try {
        await fetch(`${API}/${id}`, { method: 'DELETE' });
        await load();
      } catch (err: unknown) {
        setError(err instanceof Error ? err.message : String(err));
      }
    }
  };

  const createSubgroup = async (parentId: string, name: string, responsible: string) => {
    if (!name.trim()) return;
    try {
      await fetch(`${API}/${parentId}/subgroups`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name.trim(), responsible: responsible.trim() }),
      });
      await load();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    }
  };

  const deleteSubgroup = async (groupId: string, subId: string) => {
    try {
      await fetch(`${API}/${groupId}/subgroups/${subId}`, { method: 'DELETE' });
      await load();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    }
  };

  const styles = {
    container: {
      maxWidth: '900px',
      margin: '0 auto',
      padding: '20px',
      fontFamily: 'Arial, sans-serif',
    },
    title: {
      fontSize: '28px',
      fontWeight: 'bold',
      marginBottom: '20px',
      color: '#333',
    },
    paper: {
      border: '1px solid #ddd',
      borderRadius: '8px',
      padding: '16px',
      marginBottom: '16px',
      backgroundColor: '#fff',
      boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
    },
    error: {
      backgroundColor: '#fee',
      color: '#c00',
      padding: '12px',
      borderRadius: '4px',
      marginBottom: '16px',
      border: '1px solid #fcc',
    },
    heading: {
      fontSize: '18px',
      fontWeight: 'bold',
      marginBottom: '12px',
      color: '#222',
    },
    subHeading: {
      fontSize: '14px',
      fontWeight: 'bold',
      marginBottom: '8px',
      color: '#555',
    },
    input: {
      width: '100%',
      padding: '8px',
      marginBottom: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px',
      fontSize: '14px',
      boxSizing: 'border-box' as const,
    },
    button: {
      padding: '8px 16px',
      marginRight: '8px',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '14px',
      fontWeight: 'bold',
    },
    buttonPrimary: {
      backgroundColor: '#007bff',
      color: '#fff',
    },
    buttonOutline: {
      backgroundColor: '#fff',
      border: '1px solid #007bff',
      color: '#007bff',
    },
    buttonDanger: {
      backgroundColor: '#dc3545',
      color: '#fff',
    },
    row: {
      display: 'flex',
      gap: '8px',
      marginBottom: '8px',
      flexWrap: 'wrap' as const,
    },
    chip: {
      display: 'inline-block',
      backgroundColor: '#e9ecef',
      padding: '4px 12px',
      borderRadius: '16px',
      fontSize: '12px',
      marginRight: '4px',
      marginBottom: '4px',
    },
    groupHeader: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      marginBottom: '12px',
    },
    divider: {
      borderTop: '1px solid #eee',
      margin: '12px 0',
    },
    subgroupContainer: {
      marginLeft: '16px',
      marginTop: '12px',
    },
  };

  const GroupCard = ({ group, parentId }: { group: Group; parentId?: string }) => (
    <div className="paper">
      <div className="group-header">
        <div>
          <div className="group-title">{group.name}</div>
          <div style={{ fontSize: '12px', color: '#666' }}>
            Sorumlu: {group.responsible || '-'}
          </div>
        </div>
        <div>
          <button
            className="button button-success"
            onClick={() => setEditingGroup({ id: group.id, name: group.name, responsible: group.responsible })}
          >
            Düzenle
          </button>
          <button
            className="button button-danger"
            onClick={() => deleteGroup(group.id)}
          >
            Sil
          </button>
        </div>
      </div>

      {editingGroup?.id === group.id && (
        <div className="editing-form">
          <input
            type="text"
            className="input"
            value={editingGroup.name}
            onChange={e => setEditingGroup({ ...editingGroup, name: e.target.value })}
            placeholder="Grup adı"
          />
          <input
            type="text"
            className="input"
            value={editingGroup.responsible}
            onChange={e => setEditingGroup({ ...editingGroup, responsible: e.target.value })}
            placeholder="Sorumlu"
          />
          <div className="form-actions">
            <button
              className="button button-primary"
              onClick={() => updateGroup(editingGroup.id, editingGroup.name, editingGroup.responsible)}
            >
              Kaydet
            </button>
            <button
              className="button button-outline"
              onClick={() => setEditingGroup(null)}
            >
              İptal
            </button>
          </div>
        </div>
      )}

      <div className="divider" />

      <div className="subheading">Üyeler ({group.members?.length || 0}):</div>
      <div style={{ marginBottom: '12px' }}>
        {group.members?.map(member => (
          <span key={member.id} className="chip">
            {member.name}
          </span>
        ))}
      </div>

      {group.subgroups && group.subgroups.length > 0 && (
        <div className="subgroup-container">
          <div className="divider" />
          <div className="subheading">Alt Gruplar:</div>
          {group.subgroups.map(sg => (
            <div key={sg.id} className="paper">
              <div className="group-header">
                <div>
                  <div className="group-title">{sg.name}</div>
                  <div style={{ fontSize: '12px', color: '#666' }}>
                    Sorumlu: {sg.responsible || '-'}
                  </div>
                </div>
                <button
                  className="button button-danger"
                  onClick={() => parentId && deleteSubgroup(parentId, sg.id)}
                >
                  Sil
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );

  return (
    <div className="groups-container">
      <h1 className="title">Grup Yönetimi</h1>

      {error && <div className="error">{error}</div>}
      {loading && <p>Yükleniyor...</p>}

      {!loading && (
        <>
          <div className="paper">
            <div className="group-title">Yeni Grup Ekle</div>
            <input
              type="text"
              className="input"
              placeholder="Grup Adı"
              value={newGroupName}
              onChange={e => setNewGroupName(e.target.value)}
            />
            <input
              type="text"
              className="input"
              placeholder="Sorumlu"
              value={newGroupResponsible}
              onChange={e => setNewGroupResponsible(e.target.value)}
            />
            <button
              className="button button-primary"
              onClick={createGroup}
            >
              Grup Ekle
            </button>
          </div>

          <div>
            {groups.map(g => (
              <GroupCard key={g.id} group={g} parentId={g.id} />
            ))}
          </div>
        </>
      )}
    </div>
  );
};

export default Groups;
import React, { useEffect, useState } from 'react';
import '../styles/common.css';

interface Member {
  id: string;
  name: string;
}

interface Group {
  id: string;
  name: string;
  responsible: string;
  members: Member[];
}

interface DailyPrayerEntry {
  id: string;
  memberId: string;
  memberName: string;
  groupId: string;
  date: string;
  hijriYear: number;
  hijriMonth: number;
  prayers: {
    fajr: { done: boolean; atMosque: boolean; points: number };
    dhuhr: { done: boolean; atMosque: boolean; points: number };
    asr: { done: boolean; atMosque: boolean; points: number };
    maghrib: { done: boolean; atMosque: boolean; points: number };
    isha: { done: boolean; atMosque: boolean; points: number };
  };
}

const GROUPS_API = 'http://localhost:5000/api/groups';
const STORAGE_KEY = 'duty-scout-prayer-points';

const prayerTypes = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as const;
const prayerValues = {
  fajr: { name: 'Sabah', basePoints: 5 },
  dhuhr: { name: 'Öğle', basePoints: 3 },
  asr: { name: 'İkindi', basePoints: 3 },
  maghrib: { name: 'Akşam', basePoints: 4 },
  isha: { name: 'Yatsı', basePoints: 5 },
};

function getTodayHijri() {
  const today = new Date();
  const hijriDate = gregorianToHijri(today.getFullYear(), today.getMonth() + 1, today.getDate());
  return `${hijriDate.year}-${String(hijriDate.month).padStart(2, '0')}-${String(hijriDate.day).padStart(2, '0')}`;
}

function gregorianToHijri(gy: number, gm: number, gd: number) {
  let g_d_n = 367 * gy - Math.floor((gy + Math.floor((gm + 9) / 12)) * 0.75) + gd - 32045;
  let h_y = Math.floor((30 * g_d_n + 10646) / 10646.93);
  let h_m = Math.floor(((g_d_n % 30) + 29) / 29.5001);
  let h_d = g_d_n - Math.floor(29.5001 * h_m) - Math.floor(30 * h_y) + 1;
  return { year: h_y, month: h_m, day: h_d };
}

const PrayerPoints: React.FC = () => {
  const [groups, setGroups] = useState<Group[]>([]);
  const [selectedGroup, setSelectedGroup] = useState<string>('');
  const [entries, setEntries] = useState<DailyPrayerEntry[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [selectedDate, setSelectedDate] = useState(getTodayHijri());
  const [selectedMember, setSelectedMember] = useState<string>('');

  // Load from localStorage on mount
  useEffect(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
      try {
        const data = JSON.parse(stored);
        setEntries(data);
      } catch (e) {
        console.error('Failed to load prayer points from storage', e);
      }
    }
    loadGroups();
  }, []);

  // Save to localStorage whenever entries change
  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(entries));
  }, [entries]);

  const loadGroups = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(GROUPS_API);
      if (!res.ok) throw new Error(`Server returned ${res.status}`);
      const data = await res.json();
      setGroups(data);
      if (data.length > 0) setSelectedGroup(data[0].id);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : String(err));
    } finally {
      setLoading(false);
    }
  };

  const createEmptyEntry = (): DailyPrayerEntry => {
    const hijriDate = selectedDate.split('-').map(Number);
    return {
      id: Math.random().toString(36).substr(2, 9),
      memberId: selectedMember,
      memberName: groups
        .find(g => g.id === selectedGroup)
        ?.members.find(m => m.id === selectedMember)?.name || '',
      groupId: selectedGroup,
      date: selectedDate,
      hijriYear: hijriDate[0],
      hijriMonth: hijriDate[1],
      prayers: {
        fajr: { done: false, atMosque: false, points: 0 },
        dhuhr: { done: false, atMosque: false, points: 0 },
        asr: { done: false, atMosque: false, points: 0 },
        maghrib: { done: false, atMosque: false, points: 0 },
        isha: { done: false, atMosque: false, points: 0 },
      },
    };
  };

  const addOrEditEntry = () => {
    if (!selectedMember || !selectedGroup) {
      setError('Lütfen grup ve üye seçiniz');
      return;
    }

    const existing = entries.find(
      e => e.memberId === selectedMember && e.date === selectedDate && e.groupId === selectedGroup
    );

    if (!existing) {
      const newEntry = createEmptyEntry();
      setEntries([...entries, newEntry]);
      setEditingId(newEntry.id);
    } else {
      setEditingId(existing.id);
    }
    setError(null);
  };

  const updatePrayer = (entryId: string, prayerType: typeof prayerTypes[number], field: string, value: any) => {
    setEntries(
      entries.map(e => {
        if (e.id === entryId) {
          const updated = { ...e };
          const prayer = updated.prayers[prayerType];
          if (field === 'done') {
            prayer.done = value;
            if (value) {
              prayer.points = prayerValues[prayerType].basePoints * (prayer.atMosque ? 2 : 1);
            } else {
              prayer.points = 0;
            }
          } else if (field === 'atMosque') {
            prayer.atMosque = value;
            if (prayer.done) {
              prayer.points = prayerValues[prayerType].basePoints * (value ? 2 : 1);
            }
          }
          return updated;
        }
        return e;
      })
    );
  };

  const deleteEntry = (entryId: string) => {
    if (window.confirm('Bu girişi silmek istediğinize emin misiniz?')) {
      setEntries(entries.filter(e => e.id !== entryId));
      setEditingId(null);
    }
  };

  const currentGroup = groups.find(g => g.id === selectedGroup);
  const currentMembers = currentGroup?.members || [];

  // Get entries sorted by date and member
  const sortedEntries = [...entries]
    .filter(e => e.groupId === selectedGroup)
    .sort((a, b) => {
      const dateCompare = new Date(b.date).getTime() - new Date(a.date).getTime();
      if (dateCompare !== 0) return dateCompare;
      return a.memberName.localeCompare(b.memberName);
    });

  const getTotalPoints = (entry: DailyPrayerEntry) => {
    return Object.values(entry.prayers).reduce((sum, p) => sum + p.points, 0);
  };

  const getMonthlyTotal = (memberId: string, year: number, month: number) => {
    return entries
      .filter(e => e.memberId === memberId && e.hijriYear === year && e.hijriMonth === month && e.groupId === selectedGroup)
      .reduce((sum, e) => sum + getTotalPoints(e), 0);
  };

  const monthlyTotals = new Map<string, number>();
  entries
    .filter(e => e.groupId === selectedGroup)
    .forEach(e => {
      const key = `${e.memberId}-${e.hijriYear}-${e.hijriMonth}`;
      monthlyTotals.set(key, (monthlyTotals.get(key) || 0) + getTotalPoints(e));
    });

  return (
    <div className="prayer-points-container">
      <h1 className="title">Namaz Puanları</h1>

      {error && <div className="error">{error}</div>}
      {loading && <p>Yükleniyor...</p>}

      <div className="paper">
        <h2>Puan Kaydı Ekle/Düzenle</h2>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: '12px', marginBottom: '16px' }}>
          <div className="form-group" style={{ margin: 0 }}>
            <label>Grup</label>
            <select
              className="input"
              value={selectedGroup}
              onChange={e => {
                setSelectedGroup(e.target.value);
                setSelectedMember('');
              }}
            >
              <option value="">Grup seçiniz</option>
              {groups.map(g => (
                <option key={g.id} value={g.id}>
                  {g.name}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group" style={{ margin: 0 }}>
            <label>Üye</label>
            <select className="input" value={selectedMember} onChange={e => setSelectedMember(e.target.value)}>
              <option value="">Üye seçiniz</option>
              {currentMembers.map(m => (
                <option key={m.id} value={m.id}>
                  {m.name}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group" style={{ margin: 0 }}>
            <label>Tarih (Hicri)</label>
            <input
              type="date"
              className="input"
              value={selectedDate}
              onChange={e => setSelectedDate(e.target.value)}
            />
          </div>

          <div style={{ display: 'flex', alignItems: 'flex-end' }}>
            <button className="button button-primary" onClick={addOrEditEntry}>
              {entries.some(
                e => e.memberId === selectedMember && e.date === selectedDate && e.groupId === selectedGroup
              )
                ? 'Düzenle'
                : 'Yeni Kayıt'}
            </button>
          </div>
        </div>
      </div>

      {/* Editing Form */}
      {editingId && (
        <div className="paper">
          <h2>Puan Düzenleme: {entries.find(e => e.id === editingId)?.memberName}</h2>
          {(() => {
            const entry = entries.find(e => e.id === editingId);
            if (!entry) return null;

            return (
              <div>
                <div style={{ marginBottom: '16px', fontSize: '14px', color: '#666' }}>
                  {new Date(entry.date + 'T00:00:00').toLocaleDateString('tr-TR')} - Hicri {entry.hijriYear}/{entry.hijriMonth}
                </div>
                <table style={{ width: '100%', borderCollapse: 'collapse', marginBottom: '16px' }}>
                  <thead>
                    <tr style={{ borderBottom: '2px solid #007bff' }}>
                      <th style={{ padding: '8px', textAlign: 'left' }}>Namaz</th>
                      <th style={{ padding: '8px', textAlign: 'center', width: '80px' }}>Kılındı</th>
                      <th style={{ padding: '8px', textAlign: 'center', width: '100px' }}>Camide</th>
                      <th style={{ padding: '8px', textAlign: 'center', width: '80px' }}>Puan</th>
                    </tr>
                  </thead>
                  <tbody>
                    {prayerTypes.map(prayerType => {
                      const prayer = entry.prayers[prayerType];
                      return (
                        <tr key={prayerType} style={{ borderBottom: '1px solid #eee' }}>
                          <td style={{ padding: '8px' }}>{prayerValues[prayerType].name}</td>
                          <td style={{ padding: '8px', textAlign: 'center' }}>
                            <input
                              type="checkbox"
                              checked={prayer.done}
                              onChange={e => updatePrayer(editingId, prayerType, 'done', e.target.checked)}
                            />
                          </td>
                          <td style={{ padding: '8px', textAlign: 'center' }}>
                            <input
                              type="checkbox"
                              checked={prayer.atMosque}
                              onChange={e => updatePrayer(editingId, prayerType, 'atMosque', e.target.checked)}
                              disabled={!prayer.done}
                            />
                          </td>
                          <td style={{ padding: '8px', textAlign: 'center', fontWeight: 'bold' }}>
                            {prayer.points}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
                <div style={{ marginBottom: '16px', textAlign: 'right', fontWeight: 'bold', fontSize: '16px' }}>
                  Günlük Toplam: {getTotalPoints(entry)} puan
                </div>
                <div style={{ display: 'flex', gap: '8px' }}>
                  <button className="button button-primary" onClick={() => setEditingId(null)}>
                    Kaydet ve Kapat
                  </button>
                  <button className="button button-danger" onClick={() => deleteEntry(editingId)}>
                    Sil
                  </button>
                </div>
              </div>
            );
          })()}
        </div>
      )}

      {/* Summary Table */}
      <div className="paper">
        <h2>Puan Özeti</h2>
        {sortedEntries.length === 0 ? (
          <p style={{ color: '#999' }}>Kayıt bulunamadı</p>
        ) : (
          <div style={{ overflowX: 'auto' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
              <thead>
                <tr style={{ backgroundColor: '#f5f5f5', borderBottom: '2px solid #007bff' }}>
                  <th style={{ padding: '10px', textAlign: 'left' }}>Üye</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>Tarih</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>Sabah</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>Öğle</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>İkindi</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>Akşam</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>Yatsı</th>
                  <th style={{ padding: '10px', textAlign: 'center', fontWeight: 'bold' }}>Toplam</th>
                  <th style={{ padding: '10px', textAlign: 'center' }}>İşlem</th>
                </tr>
              </thead>
              <tbody>
                {sortedEntries.map((entry, idx) => (
                  <tr key={entry.id} style={{ borderBottom: '1px solid #eee', backgroundColor: idx % 2 === 0 ? '#fff' : '#fafafa' }}>
                    <td style={{ padding: '10px' }}>{entry.memberName}</td>
                    <td style={{ padding: '10px', textAlign: 'center', fontSize: '13px' }}>
                      {new Date(entry.date + 'T00:00:00').toLocaleDateString('tr-TR')}
                    </td>
                    {prayerTypes.map(prayerType => (
                      <td key={prayerType} style={{ padding: '10px', textAlign: 'center' }}>
                        {entry.prayers[prayerType].done ? (
                          <span style={{ fontWeight: 'bold', color: '#28a745' }}>
                            {entry.prayers[prayerType].points}
                            {entry.prayers[prayerType].atMosque ? '🕌' : ''}
                          </span>
                        ) : (
                          <span style={{ color: '#999' }}>-</span>
                        )}
                      </td>
                    ))}
                    <td style={{ padding: '10px', textAlign: 'center', fontWeight: 'bold', color: '#007bff' }}>
                      {getTotalPoints(entry)}
                    </td>
                    <td style={{ padding: '10px', textAlign: 'center' }}>
                      <button
                        className="button button-primary"
                        style={{ padding: '4px 8px', fontSize: '12px' }}
                        onClick={() => setEditingId(entry.id)}
                      >
                        Düzenle
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Monthly Summary */}
      {sortedEntries.length > 0 && (
        <div className="paper">
          <h2>Aylık Puanlar</h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))', gap: '16px' }}>
            {currentMembers.map(member => {
              const currentHijri = selectedDate.split('-').map(Number);
              const monthlyTotal = getMonthlyTotal(member.id, currentHijri[0], currentHijri[1]);
              const hasData = entries.some(
                e => e.memberId === member.id && e.hijriYear === currentHijri[0] && e.hijriMonth === currentHijri[1]
              );

              if (!hasData) return null;

              return (
                <div
                  key={member.id}
                  style={{
                    padding: '12px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    backgroundColor: '#f9f9f9',
                  }}
                >
                  <div style={{ fontWeight: 'bold', marginBottom: '8px' }}>{member.name}</div>
                  <div style={{ fontSize: '24px', color: '#007bff', fontWeight: 'bold' }}>
                    {monthlyTotal} puan
                  </div>
                  <div style={{ fontSize: '12px', color: '#666', marginTop: '4px' }}>
                    Hicri {currentHijri[0]}/{currentHijri[1]}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
    </div>
  );
};

export default PrayerPoints;
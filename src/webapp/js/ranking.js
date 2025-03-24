document.addEventListener("DOMContentLoaded", () => {
    // 模拟后端数据
    const players = [
        { name: "Alice", playtime: 120, achievements: 45 },
        { name: "Bob", playtime: 110, achievements: 50 },
        { name: "Charlie", playtime: 105, achievements: 30 },
        { name: "David", playtime: 98, achievements: 40 },
        { name: "Eve", playtime: 96, achievements: 35 },
        { name: "Frank", playtime: 90, achievements: 60 },
        { name: "Grace", playtime: 88, achievements: 28 },
        { name: "Henry", playtime: 85, achievements: 22 },
        { name: "Ivy", playtime: 82, achievements: 20 },
        { name: "Jack", playtime: 80, achievements: 55 },
        { name: "You", playtime: 95, achievements: 38 } // 当前用户
    ];

    // 排序获取前 10 名
    const topPlaytime = [...players].sort((a, b) => b.playtime - a.playtime).slice(0, 10);
    const topAchievements = [...players].sort((a, b) => b.achievements - a.achievements).slice(0, 10);

    // 当前用户信息
    const currentUser = players.find(p => p.name === "You");
    const yourRankPlaytime = players.sort((a, b) => b.playtime - a.playtime).findIndex(p => p.name === "You") + 1;
    const yourRankAchievements = players.sort((a, b) => b.achievements - a.achievements).findIndex(p => p.name === "You") + 1;

    // 填充游戏时长排行榜
    function populateTable(tableId, data, valueKey) {
        const tableBody = document.querySelector(`#${tableId} tbody`);
        tableBody.innerHTML = "";
        data.forEach((player, index) => {
            const row = `<tr>
                <td>${index + 1}</td>
                <td>${player.name}</td>
                <td>${player[valueKey]}</td>
            </tr>`;
            tableBody.innerHTML += row;
        });
    }

    populateTable("playtime-rank", topPlaytime, "playtime");
    populateTable("achievement-rank", topAchievements, "achievements");

    // 填充当前用户排名
    document.getElementById("your-rank").innerText = `Playtime Rank: #${yourRankPlaytime}, Achievement Rank: #${yourRankAchievements}`;

    // 每周游戏时长
    const weeklyPlaytimeData = players.map(player => ({
        name: player.name,
        weeklyPlaytime: Math.floor(player.playtime * 0.1) + Math.floor(Math.random() * 10)
    }));

    populateTable("weekly-playtime", weeklyPlaytimeData, "weeklyPlaytime");
});

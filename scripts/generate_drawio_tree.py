import os
import html
import shutil
import xml.etree.ElementTree as ET

def create_drawio_file(output_path):
    root_data = {
        "title": "HỆ THỐNG QUẢN LÝ KHÓA LUẬN TỐT NGHIỆP CỬ NHÂN",
        "subtitle": "SƠ ĐỒ CÂY PHÂN CẤP CHỨC NĂNG THEO 5 VAI TRÒ (CẤP 0 → CẤP 3)"
    }

    roles_data = [
        {
            "id": "role_admin",
            "name": "1. ADMIN / GIÁO VỤ",
            "desc": "Quản trị hệ thống & nghiệp vụ chung",
            "l2": [
                {
                    "title": "Quản lý tài khoản & danh mục",
                    "l3": [
                        "Thêm, sửa, khóa tài khoản",
                        "Nhập danh sách từ file Excel",
                        "Phân quyền Trưởng khoa, Trưởng bộ môn"
                    ]
                },
                {
                    "title": "Kế hoạch & điều kiện làm khóa luận",
                    "l3": [
                        "Đăng kế hoạch và lịch trình",
                        "Lọc danh sách sinh viên đủ điều kiện",
                        "Đặt số nhóm giảng viên hướng dẫn"
                    ]
                },
                {
                    "title": "Theo dõi tiến độ & đề tài",
                    "l3": [
                        "Công bố danh sách đề tài",
                        "Theo dõi tiến độ các nhóm",
                        "Nhắc nhở nhóm nộp trễ hạn"
                    ]
                },
                {
                    "title": "Hội đồng & bảng điểm",
                    "l3": [
                        "Tạo hội đồng và in quyết định",
                        "Quản lý hồ sơ bảo vệ",
                        "Xuất bảng điểm ra file Excel"
                    ]
                }
            ]
        },
        {
            "id": "role_tk",
            "name": "2. TRƯỞNG KHOA",
            "desc": "Phê duyệt đề tài & quản lý điểm",
            "l2": [
                {
                    "title": "Duyệt danh sách đề tài",
                    "l3": [
                        "Xem và duyệt đề tài các bộ môn",
                        "Yêu cầu sửa hoặc từ chối đề tài",
                        "Xuất danh sách đề tài ra Excel"
                    ]
                },
                {
                    "title": "Quản lý Hội đồng bảo vệ",
                    "l3": [
                        "Thành lập các hội đồng chấm thi",
                        "Chia nhóm sinh viên vào hội đồng",
                        "Ký và công bố lịch bảo vệ"
                    ]
                },
                {
                    "title": "Quản lý điểm & công bố kết quả",
                    "l3": [
                        "Theo dõi tiến độ chấm điểm",
                        "Duyệt mở khóa để sửa điểm",
                        "Công bố kết quả và bảng điểm"
                    ]
                }
            ]
        },
        {
            "id": "role_tbm",
            "name": "3. TRƯỞNG BỘ MÔN",
            "desc": "Quản lý chuyên môn theo ngành",
            "l2": [
                {
                    "title": "Duyệt đề tài của Bộ môn",
                    "l3": [
                        "Xem và duyệt đề tài giảng viên gửi",
                        "Góp ý chỉnh sửa hoặc từ chối",
                        "Chuyển đề tài đạt lên Trưởng khoa"
                    ]
                },
                {
                    "title": "Quản lý phản biện đề cương",
                    "l3": [
                        "Phân công giảng viên chấm đề cương",
                        "Xem kết quả và duyệt đề cương"
                    ]
                },
                {
                    "title": "Theo dõi tiến độ bộ môn",
                    "l3": [
                        "Xem tiến độ các nhóm làm đề tài",
                        "Xem báo cáo thống kê của bộ môn"
                    ]
                }
            ]
        },
        {
            "id": "role_gv",
            "name": "4. GIẢNG VIÊN",
            "desc": "Hướng dẫn, phản biện & chấm điểm",
            "l2": [
                {
                    "title": "Quản lý đề tài & nhận nhóm",
                    "l3": [
                        "Đăng đề tài mới và đề cương",
                        "Duyệt sinh viên đăng ký đề tài",
                        "Đặt lịch hẹn gặp sinh viên"
                    ]
                },
                {
                    "title": "Hướng dẫn & chấm báo cáo (AI)",
                    "l3": [
                        "Chia các đợt nộp báo cáo",
                        "Đọc tóm tắt báo cáo bằng AI",
                        "Nhận xét và cho phép đi bảo vệ"
                    ]
                },
                {
                    "title": "Chấm phản biện đề cương",
                    "l3": [
                        "Đọc đề cương được phân công",
                        "Nhận xét và chấm điểm đề cương"
                    ]
                },
                {
                    "title": "Chấm điểm Hội đồng bảo vệ",
                    "l3": [
                        "Chấm điểm trực tuyến theo tiêu chí",
                        "Ký tên và in phiếu chấm điểm",
                        "Kiểm tra báo cáo sửa sau bảo vệ"
                    ]
                }
            ]
        },
        {
            "id": "role_sv",
            "name": "5. SINH VIÊN",
            "desc": "Làm khóa luận & báo cáo",
            "l2": [
                {
                    "title": "Quản lý nhóm sinh viên",
                    "l3": [
                        "Tạo nhóm 2 đến 3 người",
                        "Tìm và mời bạn vào nhóm",
                        "Xin vào nhóm hoặc rời nhóm"
                    ]
                },
                {
                    "title": "Chọn & đăng ký đề tài",
                    "l3": [
                        "Xem danh sách đề tài và đề cương",
                        "Gửi đăng ký đề tài cho giảng viên",
                        "Xem kết quả được duyệt hay chưa"
                    ]
                },
                {
                    "title": "Báo cáo tiến độ theo đợt",
                    "l3": [
                        "Đặt lịch gặp giảng viên hướng dẫn",
                        "Nộp file báo cáo và mã nguồn",
                        "Xem nhận xét của giảng viên"
                    ]
                },
                {
                    "title": "Nộp hồ sơ & xem kết quả",
                    "l3": [
                        "Nộp hồ sơ xin bảo vệ",
                        "Xem lịch thi và phòng bảo vệ",
                        "Xem điểm thi và nộp bản sửa cuối"
                    ]
                }
            ]
        }
    ]

    # Minimalist Black & White Bordered Style (KHÔNG ĐỔ BÓNG, VIỀN RÕ RÀNG, MŨI TÊN ĐEN)
    STYLE_ROOT = (
        "html=1;whiteSpace=wrap;rounded=1;shadow=0;"
        "fillColor=#f8fafc;strokeColor=#000000;strokeWidth=2;"
        "fontColor=#000000;fontStyle=1;fontSize=14;align=center;verticalAlign=middle;"
        "arcSize=6;"
    )

    STYLE_ROLE = (
        "html=1;whiteSpace=wrap;rounded=1;shadow=0;"
        "fillColor=#f1f5f9;strokeColor=#000000;strokeWidth=1.8;"
        "fontColor=#000000;fontStyle=1;fontSize=13;align=center;verticalAlign=middle;"
        "arcSize=6;"
    )

    STYLE_L2 = (
        "html=1;whiteSpace=wrap;rounded=1;shadow=0;"
        "fillColor=#ffffff;strokeColor=#000000;strokeWidth=1.4;"
        "fontColor=#000000;fontStyle=1;fontSize=12;align=left;spacingLeft=12;verticalAlign=middle;"
        "arcSize=4;"
    )

    STYLE_L3 = (
        "html=1;whiteSpace=wrap;rounded=1;shadow=0;"
        "fillColor=#ffffff;strokeColor=#000000;strokeWidth=1;"
        "fontColor=#000000;fontSize=11;align=left;spacingLeft=12;verticalAlign=middle;"
        "arcSize=4;"
    )

    # Black Arrows (Mũi tên đen)
    EDGE_ROOT_TO_ROLE = "edgeStyle=orthogonalEdgeStyle;rounded=1;jettySize=auto;orthogonalLoop=1;html=1;strokeColor=#000000;strokeWidth=1.8;"
    EDGE_ROLE_TO_L2   = "edgeStyle=orthogonalEdgeStyle;rounded=1;jettySize=auto;orthogonalLoop=1;html=1;strokeColor=#000000;strokeWidth=1.4;"
    EDGE_L2_TO_L3     = "edgeStyle=orthogonalEdgeStyle;rounded=1;jettySize=auto;orthogonalLoop=1;html=1;strokeColor=#000000;strokeWidth=1.0;"

    # Spacious Geometry (Rộng rãi, thoáng đãng, không bị khít)
    col_width = 380
    col_gap = 65
    start_x = 70
    root_y = 50
    root_w = 720
    root_h = 68
    role_y = 175
    role_h = 58

    l2_w = col_width
    l2_h = 42
    l3_w = col_width - 32
    l3_h = 34
    l3_indent = 32

    total_cols = len(roles_data)
    canvas_w = start_x * 2 + total_cols * col_width + (total_cols - 1) * col_gap
    root_x = (canvas_w - root_w) // 2

    mxfile = ET.Element("mxfile", host="app.diagrams.net", agent="Antigravity-AI", version="24.0.0", type="device")
    diagram = ET.SubElement(mxfile, "diagram", id="so-do-cay-chuc-nang-basic", name="Sơ đồ cây chức năng 5 Role (Basic)")
    mxGraphModel = ET.SubElement(
        diagram, "mxGraphModel",
        dx="1600", dy="900", grid="1", gridSize="10", guides="1", tooltips="1",
        connect="1", arrows="1", fold="1", page="1", pageScale="1",
        pageWidth=str(canvas_w + 100), pageHeight="1450", math="0", shadow="0"
    )
    root = ET.SubElement(mxGraphModel, "root")

    ET.SubElement(root, "mxCell", id="0")
    ET.SubElement(root, "mxCell", id="1", parent="0")

    cell_counter = 2
    def get_id(prefix="cell"):
        nonlocal cell_counter
        cid = f"{prefix}_{cell_counter}"
        cell_counter += 1
        return cid

    # Root Node (Level 0)
    root_id = "node_root"
    root_val = (
        f"<b>{html.escape(root_data['title'])}</b><br/>"
        f"<span style='font-size:11px;font-weight:normal;color:#475569;'>{html.escape(root_data['subtitle'])}</span>"
    )
    c_root = ET.SubElement(root, "mxCell", id=root_id, value=root_val, style=STYLE_ROOT, vertex="1", parent="1")
    ET.SubElement(c_root, "mxGeometry", x=str(root_x), y=str(root_y), width=str(root_w), height=str(root_h), **{"as": "geometry"})

    max_bottom_y = 0

    for col_idx, role in enumerate(roles_data):
        col_x = start_x + col_idx * (col_width + col_gap)
        role_node_id = f"node_{role['id']}"

        # Level 1 Node (Role)
        role_val = (
            f"<b>{html.escape(role['name'])}</b><br/>"
            f"<span style='font-size:11px;font-weight:normal;color:#475569;'>{html.escape(role['desc'])}</span>"
        )
        c_role = ET.SubElement(root, "mxCell", id=role_node_id, value=role_val, style=STYLE_ROLE, vertex="1", parent="1")
        ET.SubElement(c_role, "mxGeometry", x=str(col_x), y=str(role_y), width=str(col_width), height=str(role_h), **{"as": "geometry"})

        # Edge from Root to Role (Black)
        edge_r_to_role = ET.SubElement(
            root, "mxCell", id=get_id("edge_r"), value="",
            style=EDGE_ROOT_TO_ROLE,
            edge="1", parent="1", source=root_id, target=role_node_id
        )
        ET.SubElement(edge_r_to_role, "mxGeometry", relative="1", **{"as": "geometry"})

        # Vertical gap between Level 1 and first Level 2 item (40px)
        curr_y = role_y + role_h + 40

        for l2_idx, l2_item in enumerate(role["l2"]):
            l2_id = f"{role_node_id}_l2_{l2_idx}"
            l2_val = f"<b>{col_idx+1}.{l2_idx+1}. {html.escape(l2_item['title'].title())}</b>"
            c_l2 = ET.SubElement(root, "mxCell", id=l2_id, value=l2_val, style=STYLE_L2, vertex="1", parent="1")
            ET.SubElement(c_l2, "mxGeometry", x=str(col_x), y=str(curr_y), width=str(l2_w), height=str(l2_h), **{"as": "geometry"})

            # Edge from Role to Level 2 (Black)
            edge_role_to_l2 = ET.SubElement(
                root, "mxCell", id=get_id("edge_l2"), value="",
                style=EDGE_ROLE_TO_L2,
                edge="1", parent="1", source=role_node_id, target=l2_id
            )
            ET.SubElement(edge_role_to_l2, "mxGeometry", relative="1", **{"as": "geometry"})

            curr_y += l2_h + 10

            for l3_idx, l3_text in enumerate(l2_item["l3"]):
                l3_id = f"{l2_id}_l3_{l3_idx}"
                l3_val = f"• {html.escape(l3_text)}"
                c_l3 = ET.SubElement(root, "mxCell", id=l3_id, value=l3_val, style=STYLE_L3, vertex="1", parent="1")
                ET.SubElement(c_l3, "mxGeometry", x=str(col_x + l3_indent), y=str(curr_y), width=str(l3_w), height=str(l3_h), **{"as": "geometry"})

                # Edge from Level 2 to Level 3 (Black)
                edge_l2_to_l3 = ET.SubElement(
                    root, "mxCell", id=get_id("edge_l3"), value="",
                    style=EDGE_L2_TO_L3,
                    edge="1", parent="1", source=l2_id, target=l3_id
                )
                ET.SubElement(edge_l2_to_l3, "mxGeometry", relative="1", **{"as": "geometry"})

                curr_y += l3_h + 8

            curr_y += 18  # spacious gap between L2 sections

        if curr_y > max_bottom_y:
            max_bottom_y = curr_y

    mxGraphModel.set("pageHeight", str(max_bottom_y + 80))

    tree = ET.ElementTree(mxfile)
    ET.indent(tree, space="  ", level=0)
    tree.write(output_path, encoding="utf-8", xml_declaration=True)
    print(f"Generated draw.io successfully at: {output_path}")
    print(f"Canvas dimensions: {canvas_w}x{max_bottom_y + 80}")

if __name__ == "__main__":
    out_file = os.path.abspath("SO_DO_CAY_CHUC_NANG_GTMS.drawio")
    create_drawio_file(out_file)
    xml_file = os.path.abspath("SO_DO_CAY_CHUC_NANG_GTMS.xml")
    shutil.copyfile(out_file, xml_file)
    print(f"Synchronized to: {xml_file}")
